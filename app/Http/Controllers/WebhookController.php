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
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    // =========================================================================
    // LISTE & STATS
    // =========================================================================

    /**
     * Liste les webhooks avec filtres et KPI.
     *
     * GET ?direction=outbound&scope=platform&status=active&application_id=3
     */
    public function index(Request $request): JsonResponse
    {
        $query = Webhook::with(['eventTypes', 'lastDelivery', 'application', 'connector']);

        // --- Filtres ---
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

        // --- KPI ---
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
    // TYPES D'ÉVÉNEMENTS (pour les formulaires)
    // =========================================================================

    /**
     * Retourne les types d'événements disponibles,
     * filtrés par direction si fournie.
     *
     * GET /webhooks/event-types?direction=outbound
     */
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

    /**
     * Crée un webhook avec ses abonnements.
     * Si auth_method = hmac_signature, génère un secret et le retourne UNE FOIS.
     */
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

            // Et dans le retour :
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

            // Abonnements aux événements
            $webhook->eventTypes()->sync($request->event_types);

            $response = [
                'message' => 'Webhook créé.',
                'webhook' => $this->formatWebhook($webhook->fresh()->load('eventTypes')),
            ];

            // Le secret en clair n'est retourné qu'ici
            if ($plainSecret) {
                $response['secret'] = $plainSecret;
            }

            return response()->json($response, 201);
        });
    }

    /**
     * Détail complet d'un webhook avec ses dernières livraisons et stats.
     */
    public function show(Webhook $webhook): JsonResponse
    {
        $webhook->load(['eventTypes', 'application', 'connector', 'creator']);

        // 10 dernières livraisons
        $recentDeliveries = $webhook->deliveries()
            ->with('eventType')
            ->latest('delivered_at')
            ->limit(10)
            ->get()
            ->map(fn ($d) => $this->formatDelivery($d));

        // Stats de livraison globales
        $totalDeliveries   = $webhook->deliveries()->count();
        $successCount      = $webhook->deliveries()->where('success', true)->count();
        $failureCount      = $totalDeliveries - $successCount;
        $avgDuration       = $webhook->deliveries()->whereNotNull('duration_ms')->avg('duration_ms');
        $successRate       = $totalDeliveries > 0 ? round(($successCount / $totalDeliveries) * 100, 1) : null;

        // Historique des rotations de secret
        $secretHistory = $webhook->secretsHistory()
            ->with('rotatedBy')
            ->latest('rotated_at')
            ->limit(10)
            ->get()
            ->map(fn ($h) => [
                'rotated_at' => $h->rotated_at->format('d/m/Y H:i'),
                'rotated_by' => $h->rotatedBy?->name,
                'reason'     => $h->reason,
            ]);

        $data = $this->formatWebhook($webhook);
        $data['application_name'] = $webhook->application?->name;
        $data['connector_name']   = $webhook->connector?->name;
        $data['created_by_name']  = $webhook->creator?->name;
        $data['has_secret']       = !empty($webhook->secret_hash);
        $data['auth_key_prefix']  = $webhook->authKey?->key_prefix;
        $data['recent_deliveries'] = $recentDeliveries;
        $data['delivery_stats']    = [
            'total'           => $totalDeliveries,
            'success_count'   => $successCount,
            'failure_count'   => $failureCount,
            'success_rate'    => $successRate,
            'avg_duration_ms' => $avgDuration ? round($avgDuration) : null,
        ];
        $data['secret_history'] = $secretHistory;

        return response()->json($data);
    }

    /**
     * Met à jour un webhook (nom, URL, auth, événements).
     * Ne touche PAS à direction, scope, ni au secret.
     */
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
                // Si on passe de hmac à autre chose, on supprime le hash
                if ($request->auth_method !== 'hmac_signature') {
                    $updates['secret_hash'] = null;
                }
                // Si on passe de api_key à autre chose
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

            // Sync des événements
            if ($request->has('event_types')) {
                $webhook->eventTypes()->sync($request->event_types);
            }

            return response()->json([
                'message' => 'Webhook mis à jour.',
                'webhook' => $this->formatWebhook($webhook->fresh()->load('eventTypes')),
            ]);
        });
    }

    /**
     * Supprime un webhook (cascade sur subscriptions, deliveries, secrets_history).
     */
    public function destroy(Webhook $webhook): JsonResponse
    {
       try {
            $webhook->delete();
            return redirect()->route('webhook.index')->with('success', 'Webhook supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Impossible de supprimer ce webhook car il est lié à des livraisons ou des événements.');
        } 
    }

    // =========================================================================
    // ACTIONS SPÉCIFIQUES
    // =========================================================================

    /**
     * Pause / reprend un webhook (toggle).
     */
    public function toggleStatus(Webhook $webhook): JsonResponse
    {
        $newStatus = $webhook->status === 'active' ? 'paused' : 'active';
        $webhook->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'réactivé' : 'mis en pause';

        return response()->json([
            'message' => "Webhook « {$webhook->name} » {$label}.",
            'status'  => $newStatus,
        ]);
    }

    /**
     * Marque un webhook en erreur (appelé par le worker de livraison quand
     * trop d'échecs consécutifs).
     */
    public function markError(Webhook $webhook): JsonResponse
    {
        $webhook->update(['status' => 'error']);

        return response()->json([
            'message' => "Webhook « {$webhook->name} » marqué en erreur.",
        ]);
    }

    /**
     * Rotation du secret HMAC.
     * Génère un nouveau secret, logue la rotation, retourne le secret UNE FOIS.
     */
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

    /**
     * Liste paginée des livraisons d'un webhook.
     *
     * GET /webhooks/{webhook}/deliveries?success=false&page=2
     */
    public function deliveries(Request $request, Webhook $webhook): JsonResponse
    {
        $query = $webhook->deliveries()->with('eventType');

        // Filtre succès/échec
        if ($request->filled('success')) {
            $query->where('success', $request->boolean('success'));
        }

        // Filtre par événement
        if ($request->filled('event_type_id')) {
            $query->where('event_type_id', $request->event_type_id);
        }

        $deliveries = $query->latest('delivered_at')
            ->simplePaginate(15)
            ->through(fn ($d) => $this->formatDelivery($d));

        return response()->json($deliveries);
    }

    /**
     * Détail d'une livraison spécifique (payload complet).
     */
    public function deliveryDetail(Webhook $webhook, WebhookDelivery $delivery): JsonResponse
    {
        // Vérifie que la livraison appartient bien au webhook
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

    // =========================================================================
    // RELANCE D'UNE LIVRAISON ÉCHOUÉE
    // =========================================================================

    /**
     * Demande la relance d'une livraison échouée.
     * Ici on ne fait que créer un nouvel enregistrement en attente —
     * le worker de livraison s'en chargera.
     * Pour le backend complet, ce sera un dispatch de job.
     */
    public function retryDelivery(Webhook $webhook, WebhookDelivery $delivery): JsonResponse
    {
        if ($delivery->webhook_id !== $webhook->id) {
            abort(404);
        }

        if ($delivery->success) {
            return response()->json(['message' => 'Cette livraison a réussi, pas besoin de relancer.'], 400);
        }

        // Crée une nouvelle tentative
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
        // Si l'URL est vide (null), on renvoie un tiret pour l'affichage
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
        // 1. On cherche le webhook en base de données
        $webhook = Webhook::findOrFail($webhookId);

        // 2. On vérifie qu'il est bien configuré pour recevoir des données
        if ($webhook->direction !== 'inbound' || $webhook->status !== 'active') {
            return response()->json(['error' => 'Webhook inactive or not inbound'], 403);
        }

        // 3. (Sécurité basique) On récupère les données envoyées par le CRM
        $payload = $request->all();

        // 4. On enregistre la livraison dans l'historique (pour que tu le voies dans ton tableau)
        WebhookDelivery::create([
            'webhook_id'   => $webhook->id,
            'event_type_id' => $request->input('event_type_id', 1), // Tu peux adapter selon ton CRM
            'direction'     => 'inbound',
            'payload'       => json_encode($payload),
            'success'       => true,
            'delivered_at'  => now(),
        ]);

        // 5. On répond au CRM que tout s'est bien passé
        return response()->json(['message' => 'Webhook received successfully'], 200);
    }
}