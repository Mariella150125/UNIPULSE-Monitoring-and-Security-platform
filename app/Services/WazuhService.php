<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WazuhService
{
    protected $baseUrl;
    protected $user;
    protected $password;

    public function __construct()
    {
        // On récupère les identifiants de Wazuh depuis le fichier .env
        $this->baseUrl = config('services.wazuh.url');
        $this->user = config('services.wazuh.user');
        $this->password = config('services.wazuh.password');
    }

    public function isConfigured(): bool
    {
        return !empty($this->baseUrl);
    }

    // Récupère le token d'authentification de Wazuh
    private function authenticate(): ?string
    {
        try {
            $response = Http::withBasicAuth($this->user, $this->password)
                ->withoutVerifying() // Ignore le certificat SSL auto-signé de Wazuh
                ->post($this->baseUrl . '/security/user/authenticate');

            return $response->json('data')['token'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    // Récupère les vulnérabilités (CVE) d'un agent Wazuh précis
    public function getVulnerabilities(string $agentId): array
    {
        if (!$this->isConfigured() || empty($agentId)) {
            return [];
        }

        $token = $this->authenticate();
        if (!$token) {
            return [];
        }

        try {
            $response = Http::withToken($token)
                ->withoutVerifying()
                ->get($this->baseUrl . "/vulnerability/{}". $agentId); // Remplacer par la vraie route API Wazuh si besoin

            $vulns = $response->json('data')['affected_items'] ?? [];
            
            // On formatte les données pour que ça corresponde à notre tableau Blade
            return array_map(function($v) {
                return [
                    'cve'          => $v['cve'] ?? 'N/A',
                    'cvss'         => (float) ($v['cvss']['base_score'] ?? 0),
                    'severity'     => strtoupper($v['severity'] ?? 'LOW'),
                    'dependency'   => ($v['name'] ?? 'Inconnu') . ' ' . ($v['version'] ?? ''),
                    'description'  => $v['description'] ?? 'Aucune description',
                    'published_at' => ($v['published'] ?? now())->format('Y-m-d'),
                    'link'         => 'https://nvd.nist.gov/vuln/detail/' . ($v['cve'] ?? '')
                ];
            }, $vulns);

        } catch (\Exception $e) {
            return [];
        }
    }
}