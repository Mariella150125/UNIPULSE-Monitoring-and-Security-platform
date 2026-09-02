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

        // Mise à jour de l'endpoint
        $endpoint->update([
            'last_status'           => $result['status'],
            'last_response_time_ms' => $result['response_time_ms'],
            'last_checked_at'       => now(),
        ]);

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