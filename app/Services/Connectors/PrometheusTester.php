<?php

namespace App\Services\Connectors;

class PrometheusTester extends BaseConnectorTester
{
    public function test(): ConnectorTestResult
    {
        $start = microtime(true);

        try {
            // RÈGLE 13 : On interroge une vraie métrique au lieu du statut
            $response = $this->makeClient()
                ->get($this->buildUrl('/api/v1/query'), [
                    'query' => 'up'
                ]);

            $duration = (microtime(true) - $start) * 1000;

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['status'] ?? 'error';

                if ($status === 'success') {
                    // RÈGLE 14 : On ne remonte plus de fausse version
                    return ConnectorTestResult::ok(
                        message: 'Connexion réussie. Requête de métrique fonctionnelle.',
                        ms: round($duration, 2),
                        meta: [
                            'metric_tested' => 'up',
                            'result_count'  => count($data['data']['result'] ?? []),
                        ],
                    );
                }
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