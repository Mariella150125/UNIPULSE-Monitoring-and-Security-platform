<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\ApiKeyScope;
use App\Models\Application;
use App\Models\ApplicationEndpoint;
use App\Models\Webhook;
use App\Models\WebhookEventType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http as LaravelHttp;
use Illuminate\Support\Facades\Log;

class WebhookManagementController extends Controller
{
    // ═══════════════════════════════════════════════════
    //  PAGE PRINCIPALE
    // ═══════════════════════════════════════════════════

    public function index()
    {
        $since24h = now()->subDay();

        // ── KPI Endpoints ──
        $epQuery = ApplicationEndpoint::whereHas('application');
        $endpointStats = [
            'total'   => $epQuery->clone()->count(),
            'healthy' => $epQuery->clone()->where('last_status', 'success')->count(),
        ];

        // ── KPI Webhooks ──
        $webhookStats = [
            'total' => Webhook::where('direction', 'outbound')->count(),
        ];

        // ── Erreurs 24h ──
        $epErrors = $epQuery->clone()
            ->whereIn('last_status', ['timeout', 'http_4xx', 'http_5xx'])
            ->where('last_checked_at', '>=', $since24h)
            ->count();
        $whErrors = DB::table('webhook_deliveries')
            ->where('success', false)
            ->where('delivered_at', '>=', $since24h)
            ->count();
        $totalErrors24h = $epErrors + $whErrors;

        // ── KPI Sécurité API ──
        $activeKeys  = ApiKey::where('status', 'active')->count();
        $totalReqs   = DB::table('api_request_logs')->where('requested_at', '>=', $since24h)->count();
        $successReqs = DB::table('api_request_logs')
            ->where('requested_at', '>=', $since24h)
            ->where('status_code', '<', 400)
            ->count();
        $blockedReqs = DB::table('api_request_logs')
            ->where('requested_at', '>=', $since24h)
            ->where('status_code', 401)
            ->count();

        $apiStats = [
            'active_count'         => $activeKeys,
            'auth_success_rate'    => $totalReqs > 0
                ? round(($successReqs / $totalReqs) * 100, 1)
                : 100.0,
            'blocked_requests_24h' => $blockedReqs,
        ];

        // ── Événements classifiés ──
        $eventCounts = [
            'critical' => $this->countDeliveriesByPattern($since24h, '%critical%'),
            'major'    => $this->countDeliveriesByPattern($since24h, '%major%'),
            'minor'    => $this->countDeliveriesByPattern($since24h, '%minor%'),
            'info'     => $this->countDeliveriesByPattern($since24h, '%info%'),
        ];
        $maxEvents = max(array_values($eventCounts), 1);

        // ── Données des tables ──
        $endpoints = ApplicationEndpoint::with('application')
            ->whereHas('application')
            ->orderByDesc('last_checked_at')
            ->get();

        $webhooks = Webhook::with('eventTypes')
            ->where('direction', 'outbound')
            ->orderByDesc('created_at')
            ->get();

        $apiKeys = ApiKey::with('scopes')
            ->orderByDesc('created_at')
            ->get();

        return view('pages.api-webhooks', compact(
            'endpointStats', 'webhookStats', 'totalErrors24h',
            'apiStats', 'eventCounts', 'maxEvents',
            'endpoints', 'webhooks', 'apiKeys'
        ));
    }

    // ═══════════════════════════════════════════════════
    //  MF-1 + MF-2 : GÉNÉRER UNE CLÉ API
    // ═══════════════════════════════════════════════════

    public function storeKey(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'scopes'     => 'required|array|min:1',
            'scopes.*'   => ['required', 'string', 'regex:/^[a-z]+:(read|write)$/'],
            'expires_at' => 'nullable|date|after:today',
        ]);

        return DB::transaction(function () use ($validated) {
            [$apiKey, $plainKey] = ApiKey::generate(
                $validated['name'],
                Auth::id(),
                $validated['expires_at'] ?? null
            );

            foreach ($validated['scopes'] as $scopeStr) {
                [$resource, $action] = explode(':', $scopeStr);
                ApiKeyScope::create([
                    'api_key_id' => $apiKey->id,
                    'resource'   => $resource,
                    'action'     => $action,
                ]);
            }

            // MF-2 : retourné une seule fois
            return response()->json([
                'message'   => 'Clé API générée avec succès.',
                'plain_key' => $plainKey,
                'prefix'    => $apiKey->key_prefix,
                'id'        => $apiKey->id,
            ]);
        });
    }

    // ═══════════════════════════════════════════════════
    //  MF-4 : ACTIONS SUR CLÉS API
    // ═══════════════════════════════════════════════════

    public function toggleKey(ApiKey $key): JsonResponse
    {
        if ($key->status === 'revoked') {
            return response()->json([
                'error' => 'Une clé révoquée ne peut pas être réactivée.',
            ], 403);
        }

        if ($key->status === 'active') {
            $key->suspend();
            $action = 'suspendue';
        } else {
            $key->resume();
            $action = 'réactivée';
        }

        return response()->json([
            'message' => "Clé « {$key->name} » {$action}.",
            'status'  => $key->fresh()->status,
        ]);
    }

    public function regenerateKey(ApiKey $key): JsonResponse
    {
        if ($key->status === 'revoked') {
            return response()->json([
                'error' => 'Impossible de régénérer une clé révoquée.',
            ], 403);
        }

        $newPlainKey = $key->regenerate();

        return response()->json([
            'message'   => "Clé « {$key->name} » régénérée. L'ancien secret est invalide.",
            'plain_key' => $newPlainKey,
            'prefix'    => $key->fresh()->key_prefix,
        ]);
    }

    public function revokeKey(ApiKey $key): JsonResponse
    {
        $key->revoke();

        return response()->json([
            'message' => "Clé « {$key->name} » révoquée définitivement.",
        ]);
    }

    // ═══════════════════════════════════════════════════
    //  MF-5 : ENDPOINTS — CRÉER / MODIFIER
    // ═══════════════════════════════════════════════════

    public function storeEndpoint(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'application_id'    => 'required|exists:applications,id',
            'http_method'       => 'required|in:GET,POST,PUT,DELETE,HEAD,OPTIONS',
            'url'               => 'required|url|max:500',
            'frequency_seconds' => 'required|integer|min:10|max:86400',
            'headers'           => 'array',
            'headers.*.key'     => 'required_with:headers|string|max:100',
            'headers.*.value'   => 'required_with:headers|string|max:500',
        ]);

        $headers = $this->cleanHeaders($request->input('headers', []));

        $endpoint = ApplicationEndpoint::create([
            'application_id'    => $validated['application_id'],
            'http_method'       => $validated['http_method'],
            'url'               => $validated['url'],
            'frequency_seconds' => $validated['frequency_seconds'],
            'is_enabled'        => true,
            'auth_headers'      => $headers->isNotEmpty() ? $headers->toArray() : null,
            'last_status'       => 'never_checked',
        ]);

        return response()->json([
            'message' => 'Endpoint créé avec succès.',
            'id'      => $endpoint->id,
        ]);
    }

    public function updateEndpoint(Request $request, ApplicationEndpoint $endpoint): JsonResponse
    {
        $validated = $request->validate([
            'application_id'    => 'required|exists:applications,id',
            'http_method'       => 'required|in:GET,POST,PUT,DELETE,HEAD,OPTIONS',
            'url'               => 'required|url|max:500',
            'frequency_seconds' => 'required|integer|min:10|max:86400',
            'headers'           => 'array',
            'headers.*.key'     => 'required_with:headers|string|max:100',
            'headers.*.value'   => 'required_with:headers|string|max:500',
        ]);

        $headers = $this->cleanHeaders($request->input('headers', []));

        $endpoint->update([
            'application_id'    => $validated['application_id'],
            'http_method'       => $validated['http_method'],
            'url'               => $validated['url'],
            'frequency_seconds' => $validated['frequency_seconds'],
            'auth_headers'      => $headers->isNotEmpty() ? $headers->toArray() : null,
        ]);

        return response()->json(['message' => 'Endpoint mis à jour.']);
    }

    // ═══════════════════════════════════════════════════
    //  ENDPOINTS — DÉSACTIVER / RÉACTIVER / SUPPRIMER
    // ═══════════════════════════════════════════════════

    public function toggleEndpoint(ApplicationEndpoint $endpoint): JsonResponse
    {
        $endpoint->update([
            'is_enabled' => !$endpoint->is_enabled,
        ]);

        $etat = $endpoint->fresh()->is_enabled ? 'activé' : 'désactivé';

        return response()->json([
            'message'    => "Endpoint {$etat}.",
            'is_enabled' => $endpoint->fresh()->is_enabled,
        ]);
    }

    public function destroyEndpoint(ApplicationEndpoint $endpoint): JsonResponse
    {
        $endpoint->delete(); // soft delete

        return response()->json(['message' => 'Endpoint supprimé.']);
    }

    public function restoreEndpoint(ApplicationEndpoint $endpoint): JsonResponse
    {
        $endpoint->restore();

        return response()->json(['message' => 'Endpoint restauré.']);
    }

    // ═══════════════════════════════════════════════════
    //  MF-6 : TESTER UN ENDPOINT
    // ═══════════════════════════════════════════════════

    public function testEndpoint(ApplicationEndpoint $endpoint): JsonResponse
    {
        $start = microtime(true);

        try {
            $response = LaravelHttp::withHeaders($endpoint->auth_headers ?? [])
                ->timeout(10)
                ->send($endpoint->http_method, $endpoint->url);

            $durationMs = (int) ((microtime(true) - $start) * 1000);
            $statusCode = $response->status();

            $status = match (true) {
                $statusCode >= 200 && $statusCode < 300 => 'success',
                $statusCode >= 400 && $statusCode < 500 => 'http_4xx',
                $statusCode >= 500                     => 'http_5xx',
                default                                => 'success',
            };

            $body = $response->body();

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $durationMs = (int) ((microtime(true) - $start) * 1000);
            $status     = 'timeout';
            $statusCode = 0;
            $body       = null;
        }

        $endpoint->update([
            'last_status'           => $status,
            'last_response_time_ms' => $durationMs,
            'last_checked_at'       => now(),
        ]);

        return response()->json([
            'status'      => $status,
            'http_code'   => $statusCode,
            'duration_ms' => $durationMs,
            'body'        => mb_substr($body ?? '', 0, 2000),
        ]);
    }

    public function curlExample(ApplicationEndpoint $endpoint): JsonResponse
    {
        $parts   = ['<span class="api-cmd">curl</span>'];
        $parts[] = '<span class="api-flag">-X ' . $endpoint->http_method . '</span> \\';

        foreach ($endpoint->auth_headers ?? [] as $k => $v) {
            $parts[] = '&nbsp;&nbsp;<span class="api-flag">-H</span> <span class="api-str">"' . e($k) . ': ' . e($v) . '"</span> \\';
        }

        $parts[] = '&nbsp;&nbsp;<span class="api-url">"' . e($endpoint->url) . '"</span>';

        return response()->json(['html' => implode('<br>', $parts)]);
    }

    // ═══════════════════════════════════════════════════
    //  WEBHOOKS OUTBOUND — CRÉER / MODIFIER
    // ═══════════════════════════════════════════════════

    public function storeWebhook(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:100',
            'target_url'         => 'required|url|max:500',
            'auth_method'        => 'required|in:none,hmac_signature,api_key',
            'api_key_id'         => 'nullable|exists:api_keys,id',
            'min_severity_level' => 'required|integer|min:0|max:4',
            'event_types'        => 'required|array|min:1',
            'event_types.*'      => 'exists:webhook_event_types,id',
        ]);

        return DB::transaction(function () use ($validated) {
            $secretHash = $validated['auth_method'] === 'hmac_signature'
                ? hash('sha256', \Illuminate\Support\Str::random(48))
                : null;

            $webhook = Webhook::create([
                'direction'          => 'outbound',
                'scope'              => 'platform',
                'name'               => $validated['name'],
                'target_url'         => $validated['target_url'],
                'auth_method'        => $validated['auth_method'],
                'secret_hash'        => $secretHash,
                'api_key_id'         => $validated['auth_method'] === 'api_key'
                    ? $validated['api_key_id']
                    : null,
                'min_severity_level' => $validated['min_severity_level'],
                'status'             => 'active',
                'created_by'         => Auth::id(),
            ]);

            $webhook->eventTypes()->sync($validated['event_types']);

            return response()->json(['message' => 'Webhook créé avec succès.']);
        });
    }

    public function updateWebhook(Request $request, Webhook $webhook): JsonResponse
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'target_url' => 'required|url|max:500',
            'status'     => 'required|in:active,paused',
        ]);

        $webhook->update([
            'name'       => $validated['name'],
            'target_url' => $validated['target_url'],
            'status'     => $validated['status'],
        ]);

        return response()->json(['message' => 'Webhook mis à jour.']);
    }

    // ═══════════════════════════════════════════════════
    //  WEBHOOKS — DÉSACTIVER / RÉACTIVER / SUPPRIMER
    // ═══════════════════════════════════════════════════

    public function toggleWebhook(Webhook $webhook): JsonResponse
    {
        $newStatus = $webhook->status === 'active' ? 'paused' : 'active';
        $webhook->update(['status' => $newStatus]);

        $etat = $newStatus === 'active' ? 'activé' : 'mis en pause';

        return response()->json([
            'message' => "Webhook {$etat}.",
            'status'  => $webhook->fresh()->status,
        ]);
    }

    public function destroyWebhook(Webhook $webhook): JsonResponse
    {
        DB::transaction(function () use ($webhook) {
            $webhook->eventTypes()->detach();
            $webhook->deliveries()->delete();
            $webhook->secretsHistory()->delete();
            $webhook->delete(); // soft delete
        });

        return response()->json(['message' => 'Webhook supprimé.']);
    }

    public function restoreWebhook(Webhook $webhook): JsonResponse
    {
        $webhook->restore();

        return response()->json(['message' => 'Webhook restauré.']);
    }

    // ═══════════════════════════════════════════════════
    //  UTILITAIRES
    // ═══════════════════════════════════════════════════

    public function eventTypes(): JsonResponse
    {
        return response()->json(
            WebhookEventType::where('applicable_direction', 'outbound')->get()
        );
    }

    private function countDeliveriesByPattern($since, string $pattern): int
    {
        return DB::table('webhook_deliveries')
            ->join('webhook_event_types', 'webhook_deliveries.event_type_id', '=', 'webhook_event_types.id')
            ->where('webhook_deliveries.delivered_at', '>=', $since)
            ->where('webhook_event_types.code', 'like', $pattern)
            ->count();
    }

    private function cleanHeaders(array $headers): \Illuminate\Support\Collection
    {
        return collect($headers)->filter(
            fn ($h) => !empty($h['key']) && !empty($h['value'])
        )->mapWithKeys(fn ($h) => [$h['key'] => $h['value']]);
    }
}