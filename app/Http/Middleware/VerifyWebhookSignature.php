<?php

namespace App\Http\Middleware;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Models\WebhookEventType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSignature
{
    /**
     * Fenêtre de tolérance pour le timestamp (secondes).
     * Au-delà, on rejette pour éviter les attaques par rejeu.
     */
    private const TIMESTAMP_TOLERANCE = 300; // 5 minutes

    /**
     * Vérifie la signature HMAC d'un webhook entrant.
     *
     * Attendus :
     *   Header X-Webhook-Signature : sha256=<hex_digest>
     *   Header X-Webhook-Timestamp : <unix_timestamp>
     *   Header X-Webhook-Event     : <event_type_code>
     *
     * Le webhook est identifié par le paramètre de route {webhook}.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $webhook = $request->route('webhook');

        if (!$webhook || !($webhook instanceof Webhook)) {
            // Tentative de résolution depuis l'ID
            $webhookId = $request->route('webhook');
            $webhook = Webhook::find($webhookId);
        }

        if (!$webhook) {
            return $this->reject($request, $webhook ?? null, 404, 'Webhook introuvable.');
        }

        // Si pas d'authentification configurée, on laisse passer
        if ($webhook->auth_method === 'none') {
            $request->attributes->set('verified_webhook', $webhook);
            return $next($request);
        }

        // Si auth par clé API (cas moins courant pour inbound, mais possible)
        if ($webhook->auth_method === 'api_key') {
            // Délègue au middleware auth.api — ici on vérifie juste
            // que la clé correspond à celle configurée
            $apiKey = $request->attributes->get('api_key');
            if (!$apiKey || $apiKey->id !== $webhook->api_key_id) {
                return $this->reject($request, $webhook, 401, 'Clé API invalide pour ce webhook.');
            }
            $request->attributes->set('verified_webhook', $webhook);
            return $next($request);
        }

        // --- Vérification HMAC ---
        $signature = $request->header('X-Webhook-Signature');
        $timestamp = $request->header('X-Webhook-Timestamp');
        $eventCode = $request->header('X-Webhook-Event');

        if (!$signature || !$timestamp) {
            return $this->reject($request, $webhook, 401, 'Headers de signature manquants.');
        }

        // 1. Vérifier le timestamp (anti-rejeu)
        if (!is_numeric($timestamp)) {
            return $this->reject($request, $webhook, 401, 'Timestamp invalide.');
        }

        if (abs(time() - (int) $timestamp) > self::TIMESTAMP_TOLERANCE) {
            return $this->reject($request, $webhook, 401, 'Timestamp expiré. Possible attaque par rejeu.');
        }

        // 2. Extraire le hash de la signature (format : sha256=abcdef...)
        if (!str_starts_with($signature, 'sha256=')) {
            return $this->reject($request, $webhook, 401, 'Format de signature invalide.');
        }

        $providedHash = substr($signature, 7);

        // 3. Calculer la signature attendue
        //    Données signées : timestamp.payload
        $payload = $request->getContent();
        $dataToSign = $timestamp . '.' . $payload;
        $expectedHash = hash_hmac('sha256', $dataToSign, $webhook->secret_hash);

        // 4. Comparaison timing-safe
        if (!hash_equals($expectedHash, $providedHash)) {
            return $this->reject($request, $webhook, 401, 'Signature invalide.');
        }

        // 5. Vérifier que l'événement est dans les abonnements du webhook
        if ($eventCode) {
            $eventType = WebhookEventType::where('code', $eventCode)->first();
            $isSubscribed = $webhook->eventTypes()->where('code', $eventCode)->exists();

            if (!$isSubscribed) {
                return $this->reject($request, $webhook, 403, "Événement {$eventCode} non abonné sur ce webhook.");
            }

            $request->attributes->set('webhook_event_type', $eventType);
        }

        // --- Tout est validé ---
        $request->attributes->set('verified_webhook', $webhook);
        $request->attributes->set('webhook_signature_valid', true);
        $request->attributes->set('webhook_request_start', microtime(true));

        return $next($request);
    }

    /**
     * Termine la requête avec log de la livraison dans webhook_deliveries.
     */
    private function reject(Request $request, ?Webhook $webhook, int $code, string $message): Response
    {
        // Log de la livraison échouée
        if ($webhook) {
            try {
                $webhook->deliveries()->create([
                    'direction'        => 'inbound',
                    'attempt_number'   => 1, 
                    'payload'          => $request->except(['password', 'token', 'secret']),
                    'signature_valid'  => false,
                    'http_status'      => $code,
                    'success'          => false,
                    'error_message'    => $message,
                    'delivered_at'     => now(),
                ]);

                $webhook->update([
                    'last_status'      => 'signature_failed',
                    'last_delivery_at' => now(),
                ]);
            } catch (\Throwable $e) {
                logger()->error('Échec du log de livraison webhook', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'error' => [
                'code'    => $code,
                'message' => $message,
            ],
        ], $code);
    }

    /**
     * Log la livraison réussie après envoi de la réponse.
     */
    public function terminate(Request $request, Response $response): void
    {
        $webhook = $request->attributes->get('verified_webhook');
        if (!$webhook || !$request->attributes->get('webhook_signature_valid')) {
            return;
        }

        try {
            $startTime = $request->attributes->get('webhook_request_start');
            $duration  = $startTime ? (int) ((microtime(true) - $startTime) * 1000) : 0;
            $success   = $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;

            $eventType = $request->attributes->get('webhook_event_type');

            $webhook->deliveries()->create([
                'event_type_id'    => $eventType?->id,
                'direction'        => 'inbound',
                'attempt_number'   => 1, 
                'payload'          => $request->except(['password', 'token', 'secret']),
                'signature_valid'  => true,
                'http_status'      => $response->getStatusCode(),
                'success'          => $success,
                'error_message'    => $success ? null : 'Erreur de traitement interne.',
                'duration_ms'      => $duration,
                'delivered_at'     => now(),
            ]);

            $webhook->update([
                'last_status'      => $success ? 'success' : 'processing_error',
                'last_delivery_at' => now(),
            ]);
        } catch (\Throwable $e) {
            logger()->error('Échec du log de livraison webhook (terminate)', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}