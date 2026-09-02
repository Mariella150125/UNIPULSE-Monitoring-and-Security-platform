<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Models\ApiRequestLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    /**
     * Extrait la clé API depuis les headers ou le query param.
     * Ordre de priorité : Bearer → X-API-Key → query param.
     */
    private function extractKey(Request $request): ?string
    {
        // 1. Authorization: Bearer sk_live_...
        $bearer = $request->bearerToken();
        if ($bearer && str_starts_with($bearer, 'sk_live_')) {
            return $bearer;
        }

        // 2. X-API-Key: sk_live_...
        $header = $request->header('X-API-Key');
        if ($header && str_starts_with($header, 'sk_live_')) {
            return $header;
        }

        // 3. ?api_key=sk_live_... (dernier recours, moins sécurisé)
        $query = $request->query('api_key');
        if ($query && str_starts_with($query, 'sk_live_')) {
            return $query;
        }

        return null;
    }

    /**
     * Reject immédiat — termine la requête sans appeler $next.
     */
    private function reject(Request $request, int $code, string $message): Response
    {
        $request->attributes->set('api_key', null);
        $request->attributes->set('api_reject_reason', $message);

        return response()->json([
            'error' => [
                'code'    => $code,
                'message' => $message,
            ],
        ], $code);
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Marque le début pour le calcul du temps de réponse
        $request->attributes->set('api_request_start', microtime(true));

        $plainKey = $this->extractKey($request);

        if (!$plainKey) {
            return $this->reject($request, 401, 'Clé API manquante.');
        }

        $apiKey = ApiKey::authenticate($plainKey);

        if (!$apiKey) {
            return $this->reject($request, 401, 'Clé API invalide, suspendue ou révoquée.');
        }

        if ($apiKey->is_expired) {
            return $this->reject($request, 401, 'Clé API expirée.');
        }

        // --- Authentifié ---
        $request->attributes->set('api_key', $apiKey);

        // Résolution de l'utilisateur pour les contrôleurs qui font $request->user()
        $request->setUserResolver(fn () => $apiKey->user);

        // Mise à jour last_used (async via terminate pour ne pas bloquer)
        $request->attributes->set('api_key_should_touch', true);

        return $next($request);
    }

    /**
     * Exécuté APRÈS que la réponse a été envoyée au client.
     * Log la requête + met à jour last_used_at sans impacter le temps de réponse.
     */
    public function terminate(Request $request, Response $response): void
    {
        try {
            $apiKey    = $request->attributes->get('api_key');
            $startTime = $request->attributes->get('api_request_start');
            $duration  = $startTime ? (int) ((microtime(true) - $startTime) * 1000) : 0;

            // Log de la requête (même si rejetée — api_key_id sera null)
            ApiRequestLog::create([
                'api_key_id'       => $apiKey?->id,
                'endpoint'         => $request->path(),
                'method'           => $request->method(),
                'status_code'      => $response->getStatusCode(),
                'ip_address'       => $request->ip(),
                'response_time_ms' => $duration,
            ]);

            // Mise à jour last_used si authentifié
            if ($apiKey && $request->attributes->get('api_key_should_touch')) {
                $apiKey->update([
                    'last_used_at' => now(),
                    'last_used_ip' => $request->ip(),
                ]);
            }
        } catch (\Throwable $e) {
            // Ne jamais faire planter la réponse à cause d'un log
            logger()->error('Échec du log de requête API', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
