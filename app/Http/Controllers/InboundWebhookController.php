<?php
namespace App\Http\Controllers;

use App\Models\Webhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InboundWebhookController extends Controller
{
    /**
     * Reçoit un webhook entrant.
     * Le middleware VerifyWebhookSignature a déjà :
     *   - Vérifié la signature HMAC
     *   - Validé le timestamp
     *   - Vérifié l'abonnement à l'événement
     *   - Logué la livraison dans webhook_deliveries
     *
     * Ici on dispatche le traitement métier.
     */
    public function handle(Request $request): JsonResponse
    {
        /** @var Webhook $webhook */
        $webhook   = $request->attributes->get('verified_webhook');
        $eventType = $request->attributes->get('webhook_event_type');
        $payload   = $request->all();

        // Dispatch vers le bon handler selon le code événement
        // Exemple avec des événements Wazuh :
        return match ($eventType?->code) {
            'wazuh.vulnerability' => $this->handleWazuhVulnerability($webhook, $payload),
            'wazuh.alert'         => $this->handleWazuhAlert($webhook, $payload),
            'wazuh.sca'           => $this->handleWazuhSca($webhook, $payload),
            'ping'                => response()->json(['pong' => true]),
            default                => response()->json([
                'received'   => true,
                'event'      => $eventType?->code,
                'webhook_id' => $webhook->id,
            ]),
        };
    }

    private function handleWazuhVulnerability(Webhook $webhook, array $payload): JsonResponse
    {
        // TODO: Traitement métier — création d'alerte, enrichissement, etc.
        // Pour l'instant, on acknowledge et on laisse le middleware loguer le succès.

        return response()->json([
            'status'     => 'accepted',
            'event'      => 'wazuh.vulnerability',
            'processed'  => false, // passera à true quand le handler sera implémenté
        ], 202);
    }

    private function handleWazuhAlert(Webhook $webhook, array $payload): JsonResponse
    {
        return response()->json([
            'status'    => 'accepted',
            'event'     => 'wazuh.alert',
            'processed' => false,
        ], 202);
    }

    private function handleWazuhSca(Webhook $webhook, array $payload): JsonResponse
    {
        return response()->json([
            'status'    => 'accepted',
            'event'     => 'wazuh.sca',
            'processed' => false,
        ], 202);
    }
}
