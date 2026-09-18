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
        $this->baseUrl = config('services.wazuh.url');
        $this->user = config('services.wazuh.user');
        $this->password = config('services.wazuh.password');
    }

    public function isConfigured(): bool
    {
        return !empty($this->baseUrl) && !empty($this->user);
    }

    private function authenticate(): ?string
    {
        try {
            $response = Http::withBasicAuth($this->user, $this->password)
                ->withoutVerifying()
                ->post($this->baseUrl . '/security/user/authenticate');

            return $response->json('data')['token'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    // Récupère les vulnérabilités des logiciels installés sur l'OS (via l'agent Wazuh)
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
                ->get($this->baseUrl . "/vulnerability/" . $agentId, [
                    'limit' => 100
                ]);

            $vulns = $response->json('data')['affected_items'] ?? [];
            
            return array_map(function($v) {
                return [
                    'cve'          => $v['cve'] ?? 'N/A',
                    'cvss'         => (float) ($v['cvss']['base_score'] ?? 0),
                    'severity'     => strtoupper($v['severity'] ?? 'LOW'),
                    'dependency'   => ($v['package']['name'] ?? 'Inconnu') . ' ' . ($v['package']['version'] ?? ''),
                    'description'  => $v['description'] ?? 'Aucune description',
                    'published_at' => substr($v['published'] ?? now()->format('Y-m-d'), 0, 10),
                    'link'         => 'https://nvd.nist.gov/vuln/detail/' . ($v['cve'] ?? '')
                ];
            }, $vulns);

        } catch (\Exception $e) {
            return [];
        }
    }

    // Récupère le score SCA (Security Configuration Assessment)
    public function getScaScore(string $agentId): ?array
    {
        if (!$this->isConfigured() || empty($agentId)) {
            return null;
        }

        $token = $this->authenticate();
        if (!$token) {
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->withoutVerifying()
                ->get($this->baseUrl . "/sca/" . $agentId);

            $scaPolicies = $response->json('data')['affected_items'] ?? [];
            
            if (!empty($scaPolicies)) {
                $policy = $scaPolicies[0];
                $score = $policy['score'] ?? 0;
                
                return [
                    'score' => (int) $score,
                    'level' => $this->getEvaluationLevel($score),
                    'color' => $this->getScoreColor($score)
                ];
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getEvaluationLevel($score) {
        if ($score >= 90) return 'Excellent';
        if ($score >= 70) return 'Moyen';
        if ($score >= 50) return 'Faible';
        return 'Critique';
    }

    private function getScoreColor($score) {
        if ($score >= 90) return 'var(--sage-green)';
        if ($score >= 70) return 'var(--orange)';
        return 'var(--red)';
    }
}