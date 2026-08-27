<?php

namespace App\Services\Connectors;

class WazuhTester extends BaseConnectorTester
{
    public function test(): ConnectorTestResult
    {
        $start = microtime(true);

        try {
            // Les API Wazuh nécessitent un jeton JWT
            // 1) Authentification
            $authResponse = $this->makeClient()
                ->post($this->buildUrl('/security/user/authenticate'));

            $duration = (microtime(true) - $start) * 1000;

            if (! $authResponse->successful()) {
                return ConnectorTestResult::fail(
                    message: "Échec de l'authentification Wazuh (code {$authResponse->status()}) : {$authResponse->body()}",
                    ms: round($duration, 2),
                );
            }

            $token = $authResponse->json('data.token');

            if (! $token) {
                return ConnectorTestResult::fail(
                    message: "Wazuh n'a pas retourné de jeton d'authentification.",
                    ms: round($duration, 2),
                );
            }

            // 2) Vérification avec le token
            $start2 = microtime(true);
            $infoResponse = Http::timeout(10)
                ->withHeaders(['Authorization' => "Bearer {$token}"])
                ->get($this->buildUrl('/?pretty'));

            $duration2 = (microtime(true) - $start2) * 1000;

            if ($infoResponse->successful()) {
                $data = $infoResponse->json();

                return ConnectorTestResult::ok(
                    message: 'Connexion Wazuh réussie.',
                    ms: round($duration + $duration2, 2),
                    meta: [
                        'version'    => $data['data']['version'] ?? 'unknown',
                        'api_version' => $data['data']['api_version'] ?? 'unknown',
                    ],
                );
            }

            return ConnectorTestResult::fail(
                message: "Jeton obtenu mais échec de la vérification (code {$infoResponse->status()}).",
                ms: round($duration + $duration2, 2),
            );

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $duration = (microtime(true) - $start) * 1000;
            return ConnectorTestResult::fail(
                message: "Impossible de se connecter à Wazuh : {$e->getMessage()}",
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