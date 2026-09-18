<?php
namespace App\Services;

use App\Models\ApplicationEndpoint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EndpointHealthChecker
{
    /**
     * Temps maximum d'attente pour une requête (secondes).
     */
    private const TIMEOUT = 10;

    /**
     * Exécute un check santé sur un endpoint et met à jour son état.
     * Retourne le résultat brut pour affichage.
     */
    public function check(ApplicationEndpoint $endpoint): array
    {
        $start  = microtime(true);
        $result = $this->performRequest($endpoint);
        $result['response_time_ms'] = (int) ((microtime(true) - $start) * 1000);

        // On ne met à jour la base de données que si ce n'est pas un blocage SSRF
        if ($result['status'] !== 'ssrf_blocked') {
            $endpoint->update([
                'last_status'           => $result['status'],
                'last_response_time_ms' => $result['response_time_ms'],
                'last_checked_at'       => now(),
            ]);
        }

        Log::info('Endpoint health check', [
            'endpoint_id'      => $endpoint->id,
            'url'              => $endpoint->url,
            'status'           => $result['status'],
            'response_time_ms' => $result['response_time_ms'],
            'success'          => $result['success'],
        ]);

        return $result;
    }

    /**
     * Exécute la requête HTTP sans modifier l'endpoint.
     * Utile pour un test "à sec" depuis l'interface.
     */
    public function dryRun(ApplicationEndpoint $endpoint): array
    {
        $start  = microtime(true);
        $result = $this->performRequest($endpoint);
        $result['response_time_ms'] = (int) ((microtime(true) - $start) * 1000);

        return $result;
    }

    /**
     * Requête HTTP effective.
     */
    private function performRequest(ApplicationEndpoint $endpoint): array
    {
        // RÈGLE 12 : PROTECTION SSRF
        if ($this->isUnsafeUrl($endpoint->url)) {
            return [
                'success'     => false,
                'status'      => 'ssrf_blocked',
                'status_code' => 0,
                'body'        => 'URL bloquée pour des raisons de sécurité (SSRF Protection).',
                'error'       => 'SSRF Protection: Tentative d\'accès à une ressource interne interdite.',
            ];
        }

        try {
            $headers = $endpoint->auth_headers ?? [];

            $response = Http::timeout(self::TIMEOUT)
                ->withHeaders($headers)
                ->send($endpoint->http_method, $endpoint->url);

            return [
                'success'     => true,
                'status'      => $this->classifyStatus($response->status()),
                'status_code' => $response->status(),
                'body'        => $this->truncateBody($response->body()),
                'error'       => null,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return [
                'success'     => false,
                'status'      => 'timeout',
                'status_code' => 0,
                'body'        => null,
                'error'       => 'Connexion impossible : ' . $e->getMessage(),
            ];
        } catch (\Illuminate\Http\Client\RequestException $e) {
            return [
                'success'     => false,
                'status'      => 'http_5xx',
                'status_code' => $e->response?->status() ?? 0,
                'body'        => $this->truncateBody($e->response?->body()),
                'error'       => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'success'     => false,
                'status'      => 'timeout',
                'status_code' => 0,
                'body'        => null,
                'error'       => $e->getMessage(),
            ];
        }
    }

    /**
     * RÈGLE 12 : Vérifie si l'URL est potentiellement dangereuse (SSRF).
     */
    private function isUnsafeUrl(string $url): bool
    {
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';

        // 1. Bloquer localhost et les IP locales explicites
        $blockedHosts = ['localhost', '127.0.0.1', '0.0.0.0', '169.254.169.254', '::1'];
        if (in_array($host, $blockedHosts)) {
            return true;
        }

        // 2. Bloquer les plages d'IP privées (10.x, 192.168.x, 172.16-31.x)
        if (preg_match('/^(10\.|192\.168\.|172\.(1[6-9]|2[0-9]|3[0-1])\.)/', $host)) {
            return true;
        }

        return false;
    }

    /**
     * Classifie un code HTTP en statut métier.
     */
    private function classifyStatus(int $code): string
    {
        return match (true) {
            $code >= 200 && $code < 300 => 'success',
            $code >= 400 && $code < 500 => 'http_4xx',
            $code >= 500                 => 'http_5xx',
            default                      => 'http_4xx',
        };
    }

    /**
     * Tronque le corps de la réponse pour éviter de stocker/retourner des mégaoctets.
     */
    private function truncateBody(?string $body, int $maxLength = 2000): ?string
    {
        if (!$body) {
            return null;
        }

        return strlen($body) > $maxLength
            ? substr($body, 0, $maxLength) . '…'
            : $body;
    }
}