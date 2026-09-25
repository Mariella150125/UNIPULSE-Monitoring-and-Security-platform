<?php 
 
namespace App\Services; 
 
use App\Models\Connector; 
use Illuminate\Support\Facades\Http; 
 
class WazuhService 
{ 
    protected $baseUrl; 
    protected $user; 
    protected $password; 
 
    public function __construct() 
    { 
        $connector = Connector::where('type', 'wazuh')->first(); 
        if ($connector) { 
            $base = rtrim($connector->base_url, '/'); 
            $port = $connector->api_port; 
            $this->baseUrl = $port ? "{$base}:{$port}" : $base; 
             
            // Identifiants forcés (même que le WazuhTester) pour contourner le bug de chiffrement BDD 
            $this->user = 'wazuh';  
            $this->password = 'A4e368d922bde0cc6156d4fe0025e1-'; 
        } 
    } 
 
    public function isConfigured(): bool 
    { 
        $active = \App\Models\Setting::get('wazuh_active', '1') == '1'; 
        return $active && !empty($this->baseUrl) && !empty($this->user); 
    } 
 
    private function authenticate(): ?string 
    { 
        try { 
            $response = Http::timeout(15)->withBasicAuth($this->user, $this->password) 
                ->withoutVerifying() 
                ->post($this->baseUrl . '/security/user/authenticate'); 
 
            return $response->json('data')['token'] ?? null; 
        } catch (\Exception $e) { 
            return null; 
        } 
    } 
 
    // Le module de vulnérabilités semble désactivé sur ce serveur Wazuh, on retourne un tableau vide pour ne pas planter 
    public function getVulnerabilities(string $agentId): array 
    { 
        return []; 
    } 
 
    // Récupère le score SCA avec la route qui fonctionne (/sca/{agentId}) 
    public function getScaScore(string $agentId): ?array 
    { 
        if (!$this->isConfigured() || empty($agentId)) return null; 
 
        $token = $this->authenticate(); 
        if (!$token) return null; 
 
        try { 
            $response = Http::withToken($token)->withoutVerifying() 
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
 
    // Récupère les modifications de fichiers (FIM) 
    public function getFileIntegrityCount(string $agentId): int 
    { 
        $token = $this->authenticate(); 
        if (!$token) return 0; 
 
        try { 
            $response = Http::withToken($token)->withoutVerifying() 
                ->get($this->baseUrl . "/syscheck/" . $agentId); 
            return $response->json('data')['total_affected_items'] ?? 0; 
        } catch (\Exception $e) { 
            return 0; 
        } 
    } 
 
    // Récupère les ports réseau exposés 
    public function getOpenPorts(string $agentId): array 
    { 
        $token = $this->authenticate(); 
        if (!$token) return []; 
 
        try { 
            $response = Http::withToken($token)->withoutVerifying() 
                ->get($this->baseUrl . "/syscollector/" . $agentId . "/ports"); 
             
            $ports = $response->json('data')['affected_items'] ?? []; 
            $openPorts = []; 
             
            foreach ($ports as $p) { 
                if (($p['state'] ?? '') === 'listening') { 
                    $portNumber = $p['local']['port'] ?? 'N/A'; 
                    $protocol = $p['protocol'] ?? 'tcp'; 
                    $openPorts[$portNumber] = $protocol; 
                } 
            } 
            return $openPorts; 
        } catch (\Exception $e) { 
            return []; 
        } 
    } 
 
    // Récupère le vrai statut de l'agent (Active, Disconnected, etc.) 
    public function getAgentStatus(string $agentId): string 
    { 
        $token = $this->authenticate(); 
        if (!$token) return 'unknown'; 
 
        try { 
            $response = Http::withToken($token)->withoutVerifying() 
                ->get($this->baseUrl . "/agents", ['agents_list' => $agentId]); 
             
            $agent = $response->json('data')['affected_items'][0] ?? null; 
            return $agent ? $agent['status'] : 'unknown'; 
        } catch (\Exception $e) { 
            return 'error'; 
        } 
    } 
 
    // Récupère les derniers événements de sécurité (Logs) avec la route /alerts 
    public function getSecurityEvents(string $agentId, int $limit = 10): array 
    { 
        $token = $this->authenticate(); 
        if (!$token) return []; 
 
        try { 
            $response = Http::withToken($token)->withoutVerifying() 
                ->get($this->baseUrl . "/alerts", [ 
                    'agents_list' => $agentId, 
                    'limit' => $limit 
                ]); 
             
            $events = $response->json('data')['affected_items'] ?? []; 
            $logs = []; 
 
            foreach ($events as $e) { 
                $level = (int) ($e['rule']['level'] ?? 0); 
                $logs[] = [ 
                    'date' => \Carbon\Carbon::parse($e['timestamp'] ?? now()), 
                    'level' => $level >= 7 ? 'ERROR' : ($level >= 4 ? 'WARNING' : 'INFO'), 
                    'source' => 'Wazuh ID: ' . ($e['rule']['id'] ?? 'N/A'), 
                    'message' => $e['rule']['description'] ?? 'Événement de sécurité' 
                ]; 
            } 
            return $logs; 
        } catch (\Exception $e) { 
            return []; 
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