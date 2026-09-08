<?php

namespace App\Http\Controllers;

use App\Http\Requests\RotateWebhookSecretRequest;
use App\Http\Requests\StoreWebhookRequest;
use App\Http\Requests\UpdateWebhookRequest;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Models\WebhookEventType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class WebhookController extends Controller
{
    // =========================================================================
    // LISTE & STATS
    // =========================================================================

    public function index(Request $request): JsonResponse
    {
        $query = Webhook::with(['eventTypes', 'lastDelivery', 'application', 'connector']);

        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }
        if ($request->filled('scope')) {
            $query->where('scope', $request->scope);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('application_id')) {
            $query->where('application_id', $request->application_id);
        }

        $webhooks = $query->orderByDesc('created_at')->get()->map(fn ($w) => [
            'id'                  => $w->id,
            'name'                => $w->name,
            'direction'           => $w->direction,
            'scope'               => $w->scope,
            'target_url'          => $w->target_url,
            'target_url_short'    => $this->shortenUrl($w->target_url),
            'auth_method'         => $w->auth_method,
            'min_severity_level'  => $w->min_severity_level,
            'status'              => $w->status,
            'last_delivery_status'=> $w->lastDelivery?->success === true ? 'success' : ($w->lastDelivery?->success === false ? 'failed' : null),
            'last_delivery_at'    => $w->lastDelivery?->delivered_at?->diffForHumans(),
            'event_types'         => $w->eventTypes->map(fn ($et) => [
                'code'  => $et->code,
                'label' => $et->label,
            ])->values()->all(),
            'application_name'    => $w->application?->name,
            'connector_name'      => $w->connector?->name,
            'created_at'          => $w->created_at->format('d/m/Y'),
        ]);

        $last24h = now()->subDay();

        $stats = [
            'total'      => Webhook::count(),
            'active'     => Webhook::where('status', 'active')->count(),
            'paused'     => Webhook::where('status', 'paused')->count(),
            'error'      => Webhook::where('status', 'error')->count(),
            'failures_24h' => WebhookDelivery::where('delivered_at', '>=', $last24h)
                ->where('success', false)
                ->count(),
        ];

        return response()->json([
            'webhooks' => $webhooks,
            'stats'    => $stats,
        ]);
    }

    // =========================================================================
    // TYPES D'ÉVÉNEMENTS
    // =========================================================================

    public function eventTypes(Request $request): JsonResponse
    {
        $query = WebhookEventType::query();

        if ($request->filled('direction')) {
            $query->where('applicable_direction', $request->direction)
                  ->orWhere('applicable_direction', 'both');
        }

        return response()->json([
            'event_types' => $query->orderBy('code')->get()->map(fn ($et) => [
                'id'          => $et->id,
                'code'        => $et->code,
                'label'       => $et->label,
                'direction'   => $et->applicable_direction,
                'description' => $et->description,
            ]),
        ]);
    }

    // =========================================================================
    // CRUD
    // =========================================================================

    public function store(StoreWebhookRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $plainSecret = null;
            $secretHash  = null;

            if ($request->auth_method === 'hmac_signature') {
                $preHash      = \Illuminate\Support\Str::random(48);
                $sharedSecret = hash('sha256', $preHash);
                $secretHash   = $sharedSecret;
            }

            if ($sharedSecret ?? null) {
                $response['secret'] = $sharedSecret;
            }

            $webhook = Webhook::create([
                'direction'          => $request->direction,
                'scope'              => $request->scope,
                'name'               => $request->name,
                'connector_id'       => $request->connector_id,
                'application_id'     => $request->application_id,
                'target_url'         => $request->target_url,
                'auth_method'        => $request->auth_method,
                'secret_hash'        => $secretHash,
                'api_key_id'         => $request->api_key_id,
                'min_severity_level' => $request->min_severity_level ?? 0,
                'created_by'         => auth()->id(),
            ]);

            $webhook->eventTypes()->sync($request->event_types);

            $response = [
                'message' => 'Webhook créé.',
                'webhook' => $this->formatWebhook($webhook->fresh()->load('eventTypes')),
            ];

            if ($plainSecret) {
                $response['secret'] = $plainSecret;
            }

            return response()->json($response, 201);
        });
    }

    public function editData(Webhook $webhook): JsonResponse
    {
        $webhook->load('eventTypes');
        
        return response()->json([
            'id'                 => $webhook->id,
            'name'               => $webhook->name,
            'application_id'     => $webhook->application_id,
            'auth_method'        => $webhook->auth_method,
            'api_key_id'         => $webhook->api_key_id,
            'min_severity_level' => $webhook->min_severity_level,
            'event_types'        => $webhook->eventTypes->pluck('id')->toArray(),
        ]);
    }

    /**
     * Affiche la page de détail du Webhook (Vue HTML)
     */
    public function show(Webhook $webhook)
    {
        $webhook->load(['eventTypes', 'application', 'connector', 'creator']);

        $recentDeliveries = $webhook->deliveries()
            ->with('eventType')
            ->latest('delivered_at')
            ->limit(10)
            ->get();

        $totalDeliveries = $webhook->deliveries()->count();
        $successCount    = $webhook->deliveries()->where('success', true)->count();
        $failureCount    = $totalDeliveries - $successCount;
        $avgDuration     = $webhook->deliveries()->whereNotNull('duration_ms')->avg('duration_ms');
        $successRate     = $totalDeliveries > 0 ? round(($successCount / $totalDeliveries) * 100, 1) : null;

        $secretHistory = $webhook->secretsHistory()
            ->with('rotatedBy')
            ->latest('rotated_at')
            ->limit(10)
            ->get();

        return view('administration.webhook.show', compact(
            'webhook',
            'recentDeliveries',
            'totalDeliveries',
            'successCount',
            'failureCount',
            'avgDuration',
            'successRate',
            'secretHistory'
        ));
    }

    public function update(UpdateWebhookRequest $request, Webhook $webhook): JsonResponse
    {
        return DB::transaction(function () use ($request, $webhook) {
            $updates = [];

            if ($request->has('name')) {
                $updates['name'] = $request->name;
            }
            if ($request->has('target_url')) {
                $updates['target_url'] = $request->target_url;
            }
            if ($request->has('auth_method')) {
                $updates['auth_method'] = $request->auth_method;
                if ($request->auth_method !== 'hmac_signature') {
                    $updates['secret_hash'] = null;
                }
                if ($request->auth_method !== 'api_key') {
                    $updates['api_key_id'] = null;
                }
            }
            if ($request->has('api_key_id')) {
                $updates['api_key_id'] = $request->api_key_id;
            }
            if ($request->has('min_severity_level')) {
                $updates['min_severity_level'] = $request->min_severity_level;
            }

            if (!empty($updates)) {
                $webhook->update($updates);
            }

            if ($request->has('event_types')) {
                $webhook->eventTypes()->sync($request->event_types);
            }

            return response()->json([
                'message' => 'Webhook mis à jour.',
                'webhook' => $this->formatWebhook($webhook->fresh()->load('eventTypes')),
            ]);
        });
    }

    public function destroy(Webhook $webhook)
    {
       $webhook->delete();
       return redirect()->route('webhooks.page')->with('success', 'Webhook supprimé avec succès.');
    }

    // =========================================================================
    // ACTIONS SPÉCIFIQUES
    // =========================================================================

    public function toggleStatus(Request $request, Webhook $webhook)
    {
        $newStatus = $request->input('status', $webhook->status === 'active' ? 'paused' : 'active');
        $webhook->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'activé' : 'désactivé';

        return redirect()->back()->with('success', "Webhook « {$webhook->name} » {$label} avec succès.");
    }

    public function markError(Webhook $webhook): JsonResponse
    {
        $webhook->update(['status' => 'error']);

        return response()->json([
            'message' => "Webhook « {$webhook->name} » marqué en erreur.",
        ]);
    }

    public function rotateSecret(RotateWebhookSecretRequest $request, Webhook $webhook): JsonResponse
    {
        if ($webhook->auth_method !== 'hmac_signature') {
            return response()->json([
                'message' => 'La rotation de secret ne s\'applique qu\'aux webhooks HMAC.',
            ], 422);
        }

        $plainSecret = $webhook->rotateSecret(
            rotatedBy: auth()->id(),
            reason:    $request->reason,
        );

        return response()->json([
            'message' => 'Secret rotaté. L\'ancien secret est invalide.',
            'secret'  => $plainSecret,
        ]);
    }

    // =========================================================================
    // HISTORIQUE DES LIVRAISONS
    // =========================================================================

    public function deliveries(Request $request, Webhook $webhook): JsonResponse
    {
        $query = $webhook->deliveries()->with('eventType');

        if ($request->filled('success')) {
            $query->where('success', $request->boolean('success'));
        }

        if ($request->filled('event_type_id')) {
            $query->where('event_type_id', $request->event_type_id);
        }

        $deliveries = $query->latest('delivered_at')
            ->simplePaginate(15)
            ->through(fn ($d) => $this->formatDelivery($d));

        return response()->json($deliveries);
    }

    public function deliveryDetail(Webhook $webhook, WebhookDelivery $delivery): JsonResponse
    {
        if ($delivery->webhook_id !== $webhook->id) {
            abort(404);
        }

        $delivery->load('eventType');

        return response()->json([
            'id'                => $delivery->id,
            'event_type'        => $delivery->eventType?->only('code', 'label'),
            'direction'         => $delivery->direction,
            'attempt_number'    => $delivery->attempt_number,
            'payload'           => $delivery->payload,
            'signature_valid'   => $delivery->signature_valid,
            'http_status'       => $delivery->http_status,
            'success'           => $delivery->success,
            'error_message'     => $delivery->error_message,
            'duration_ms'       => $delivery->duration_ms,
            'delivered_at'      => $delivery->delivered_at->format('d/m/Y H:i:s'),
        ]);
    }

    public function retryDelivery(Webhook $webhook, WebhookDelivery $delivery): JsonResponse
    {
        if ($delivery->webhook_id !== $webhook->id) {
            abort(404);
        }

        if ($delivery->success) {
            return response()->json(['message' => 'Cette livraison a réussi, pas besoin de relancer.'], 400);
        }

        $retry = $webhook->deliveries()->create([
            'event_type_id'    => $delivery->event_type_id,
            'direction'        => $delivery->direction,
            'attempt_number'   => $delivery->attempt_number + 1,
            'payload'          => $delivery->payload,
            'success'          => false,
        ]);

        return response()->json([
            'message'         => 'Relance programmée.',
            'retry_delivery_id' => $retry->id,
        ], 201);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function formatWebhook(Webhook $w): array
    {
        return [
            'id'                 => $w->id,
            'name'               => $w->name,
            'direction'          => $w->direction,
            'scope'              => $w->scope,
            'application_id'     => $w->application_id,
            'connector_id'       => $w->connector_id,
            'target_url'         => $w->target_url,
            'target_url_short'   => $this->shortenUrl($w->target_url),
            'auth_method'        => $w->auth_method,
            'min_severity_level' => $w->min_severity_level,
            'status'             => $w->status,
            'last_status'        => $w->last_status,
            'last_delivery_at'   => $w->last_delivery_at?->diffForHumans(),
            'event_types'        => $w->eventTypes->map(fn ($et) => [
                'id'    => $et->id,
                'code'  => $et->code,
                'label' => $et->label,
            ])->values()->all(),
            'created_at'         => $w->created_at->format('d/m/Y'),
        ];
    }

    private function formatDelivery(WebhookDelivery $d): array
    {
        return [
            'id'              => $d->id,
            'event_type'      => $d->eventType?->only('code', 'label'),
            'direction'       => $d->direction,
            'attempt_number'  => $d->attempt_number,
            'signature_valid' => $d->signature_valid,
            'http_status'     => $d->http_status,
            'success'         => $d->success,
            'error_message'   => $d->error_message,
            'duration_ms'     => $d->duration_ms,
            'delivered_at'    => $d->delivered_at->diffForHumans(),
        ];
    }

    private function shortenUrl(?string $url, int $max = 50): string
    {
        if (!$url) {
            return '—';
        }

        if (strlen($url) <= $max) {
            return $url;
        }

        $parsed = parse_url($url);
        $host   = $parsed['host'] ?? $url;
        $path   = $parsed['path'] ?? '';

        if (strlen($host . $path) <= $max) {
            return $host . $path;
        }

        return $host . '/…';
    }
    
    public function receive(Request $request, $webhookId)
    {
        $webhook = Webhook::findOrFail($webhookId);

        if ($webhook->direction !== 'inbound' || $webhook->status !== 'active') {
            return response()->json(['error' => 'Webhook inactive or not inbound'], 403);
        }

        $payload = $request->all();

        WebhookDelivery::create([
            'webhook_id'   => $webhook->id,
            'event_type_id' => $request->input('event_type_id', 1),
            'direction'     => 'inbound',
            'payload'       => json_encode($payload),
            'success'       => true,
            'delivered_at'  => now(),
        ]);

        return response()->json(['message' => 'Webhook received successfully'], 200);
    }
}