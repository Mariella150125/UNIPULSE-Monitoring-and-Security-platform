<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\ApplicationEndpoint;
use App\Models\Webhook;
use Illuminate\Support\Facades\DB;
use App\Models\WebhookEventType;

class WebhookPageController extends Controller
{
    /**
     * Prépare toutes les données pour la vue de gestion API & Webhooks.
     * Ne gère aucune action CRUD — c'est le rôle des contrôleurs dédiés.
     */
    public function index()
    {
        $since24h = now()->subDay();

        // ── KPIs Endpoints ──
        $epQuery = ApplicationEndpoint::whereHas('application');

        $endpointStats = [
            'total'   => $epQuery->clone()->count(),
            'healthy' => $epQuery->clone()->where('last_status', 'success')->count(),
        ];

        // ── KPIs Webhooks ──
        $webhookStats = [
            'total' => Webhook::where('direction', 'outbound')->count(),
        ];

        // ── Erreurs 24h (endpoints en erreur + livraisons webhook échouées) ──
        $totalErrors24h = $epQuery->clone()
            ->whereIn('last_status', ['timeout', 'http_4xx', 'http_5xx'])
            ->where('last_checked_at', '>=', $since24h)
            ->count()
            + DB::table('webhook_deliveries')
                ->where('success', false)
                ->where('delivered_at', '>=', $since24h)
                ->count();

        // ── Sécurité API ──
        $totalReqs = DB::table('api_request_logs')
            ->where('requested_at', '>=', $since24h)
            ->count();

        $successReqs = DB::table('api_request_logs')
            ->where('requested_at', '>=', $since24h)
            ->where('status_code', '<', 400)
            ->count();

        $apiStats = [
            'active_count'         => ApiKey::where('status', 'active')->count(),
            'auth_success_rate'    => $totalReqs > 0
                ? round(($successReqs / $totalReqs) * 100, 1)
                : 100.0,
            'blocked_requests_24h' => DB::table('api_request_logs')
                ->where('requested_at', '>=', $since24h)
                ->where('status_code', 401)
                ->count(),
        ];

        // ── Événements classifiés ──
        $eventCounts = [
            'critical' => $this->countDeliveries($since24h, '%critical%'),
            'major'    => $this->countDeliveries($since24h, '%major%'),
            'minor'    => $this->countDeliveries($since24h, '%minor%'),
            'info'     => $this->countDeliveries($since24h, '%info%'),
        ];
        $maxEvents = max(array_values($eventCounts));
        $maxEvents = max(1, (int) $maxEvents);

        // ── Données des tableaux ──
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
        $webhookEventTypes = WebhookEventType::orderBy('code')->get();

        return view('administration.webhook.webh', compact(
            'endpointStats', 'webhookStats', 'totalErrors24h',
            'apiStats', 'eventCounts', 'maxEvents',
            'endpoints', 'webhooks', 'apiKeys','webhookEventTypes'
        ));
    }

    private function countDeliveries($since, string $pattern): int
    {
        return DB::table('webhook_deliveries')
            ->join('webhook_event_types', 'webhook_deliveries.event_type_id', '=', 'webhook_event_types.id')
            ->where('webhook_deliveries.delivered_at', '>=', $since)
            ->where('webhook_event_types.code', 'like', $pattern)
            ->count();
    }
}