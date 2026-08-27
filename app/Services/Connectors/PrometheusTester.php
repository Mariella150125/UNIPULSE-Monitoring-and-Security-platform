<?php

namespace App\Services\Connectors;

class PrometheusTester extends BaseConnectorTester
{
    public function test(): ConnectorTestResult
    {
        $start = microtime(true);

        try {
            $response = $this->makeClient()
                ->get($this->buildUrl('/api/v1/status/config'));

            $duration = (microtime(true) - $start) * 1000;

            if ($response->successful()) {
                $data = $response->json();

                return ConnectorTestResult::ok(
                    message: 'Connexion Prometheus réussie.',
                    ms: round($duration, 2),
                    meta: [
                        'version'   => $data['data']['status'] ?? 'unknown',
                        'prometheus' => $data['data']['config'] ?? null,
                    ],
                );
            }

            return ConnectorTestResult::fail(
                message: "Prometheus a répondu avec le code {$response->status()} : {$response->body()}",
                ms: round($duration, 2),
            );

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $duration = (microtime(true) - $start) * 1000;
            return ConnectorTestResult::fail(
                message: "Impossible de se connecter à Prometheus : {$e->getMessage()}",
                ms: round($duration, 2),
            );
        } catch (\Throwable $e) {
            $duration = (microtime(true) - $start) * 1000;
            return ConnectorTestResult::fail(
                message: "Erreur inattendue : {$e->getMessage()}",
                ms: round($duration, 2),
            );
        }
    }
}