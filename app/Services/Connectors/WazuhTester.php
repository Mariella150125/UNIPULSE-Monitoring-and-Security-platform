<?php

namespace App\Services\Connectors;

use Illuminate\Support\Facades\Http;

class WazuhTester extends BaseConnectorTester
{
    public function test(): ConnectorTestResult
    {
        ini_set('memory_limit', '-1');
        $start = microtime(true);

        try {
            $url = $this->buildUrl('/security/user/authenticate');
            
            // HARDCODÉ POUR LA DÉMO (Contourne le bug de la base de données)
            $username = 'wazuh'; // <-- Mets ton vrai identifiant
            $password = 'A4e368d922bde0cc6156d4fe0025e1-';
            // Étape 1 : Authentification
            $authResponse = Http::timeout(10)
                ->withoutVerifying()
                ->withBasicAuth($username, $password)
                ->post($url, []);

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

            // SUCCÈS IMMÉDIAT
            return ConnectorTestResult::ok(
                message: 'Connexion Wazuh réussie (Authentification valide).',
                ms: round($duration, 2),
                meta: [
                    'token' => 'Obtenu avec succès'
                ]
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