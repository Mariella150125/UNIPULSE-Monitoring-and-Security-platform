<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Server;
use App\Services\WazuhService;
use Illuminate\Support\Facades\Http;

class ScoringService
{
    protected $wazuhService;

    public function __construct(WazuhService $wazuhService)
    {
        $this->wazuhService = $wazuhService;
    }

    public function getAppScore(Application $app): array
    {
        $score = 0;
        $checks = [];

        // 1. HTTPS obligatoire (Poids: 10)
        $isHttps = $app->url && str_starts_with($app->url, 'https://');
        $checks[] = $this->createCheck('HTTPS obligatoire', 10, $isHttps, 'Activer la redirection HTTP vers HTTPS.', 'https://owasp.org/www-project-application-security-verification-standard/');
        $score += $isHttps ? 10 : 0;

        $isAvailable = false;
        $sslValid = false;
        $headersValid = false;
        $headersDetails = [];

        if ($app->url) {
            // 2. Disponibilité HTTPS (HTTP 2xx)
            try {
                $response = Http::timeout(3)->withoutVerifying()->get($app->url);
                $isAvailable = $response->successful();
            } catch (\Exception $e) {
                $isAvailable = false;
            }

            // 3. Validité du Certificat SSL (Vraie vérification TLS)
            if ($isHttps) {
                $parsedUrl = parse_url($app->url);
                $host = $parsedUrl['host'] ?? null;
                $port = $parsedUrl['port'] ?? 443;

                if ($host) {
                    $context = stream_context_create(["ssl" => ["capture_peer_cert" => true]]);
                    $stream = @stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, 3, STREAM_CLIENT_CONNECT, $context);
                    
                    if ($stream) {
                        $params = stream_context_get_params($stream);
                        $peerCert = $params['options']['ssl']['peer_certificate'] ?? null;
                        
                        if ($peerCert) {
                            $cert = openssl_x509_parse($peerCert);
                            if ($cert && isset($cert['validTo_time_t']) && $cert['validTo_time_t'] > time()) {
                                $sslValid = true;
                            }
                        }
                    }
                }
            }

            // 4. En-têtes de Sécurité (HSTS, CSP, X-Frame-Options)
            if ($isAvailable) {
                $hsts = $response->header('Strict-Transport-Security');
                $csp = $response->header('Content-Security-Policy');
                $xfo = $response->header('X-Frame-Options');
                
                $headersDetails = [
                    'HSTS' => !empty($hsts),
                    'CSP' => !empty($csp),
                    'X-Frame-Options' => !empty($xfo)
                ];
                
                $validCount = count(array_filter($headersDetails));
                $headersValid = ($validCount >= 2);
            }
        }

        // Contrôle Disponibilité (Poids: 10)
        $checks[] = $this->createCheck('Disponibilité HTTPS', 10, $isAvailable, 'Vérifier que l\'application répond en HTTP 200.', 'https://owasp.org/www-project-application-security-verification-standard/');
        $score += $isAvailable ? 10 : 0;

        // Contrôle Certificat SSL (Poids: 10)
        $checks[] = $this->createCheck('Certificat SSL valide', 10, $sslValid, 'Renouveler ou corriger le certificat SSL (non expiré).', 'https://owasp.org/www-project-application-security-verification-standard/');
        $score += $sslValid ? 10 : 0;

        // Contrôle En-têtes HTTP (Poids: 15)
        $headersStatus = $headersValid ? 'Conforme' : 'Non conforme';
        if (!empty($headersDetails)) {
            $missing = array_keys(array_filter($headersDetails, function($v) { return !$v; }));
            if (!empty($missing)) $headersStatus .= ' (Manquants: ' . implode(', ', $missing) . ')';
        }
        $checks[] = $this->createCheck('En-têtes de sécurité (HSTS, CSP, X-Frame)', 15, $headersValid, 'Activer les en-têtes de sécurité essentiels.', 'https://owasp.org/www-project-secure-headers/', $headersStatus);
        $score += $headersValid ? 15 : 0;

        // Contrôle Dépendances (Règle 5 : Non évalué sans SCA)
        $checks[] = $this->createCheck('Analyse des dépendances (SCA)', 20, false, 'Intégrer un outil d\'analyse des dépendances (composer.lock).', 'https://owasp.org/www-project-dependency-check/', 'Non évalué');
        
        // Autres contrôles (Non évalués)
        $checks[] = $this->createCheck('Authentification sécurisée', 10, false, 'Renforcer la politique d\'authentification.', 'https://owasp.org/www-project-authentication-cheat-sheet/', 'Non évalué');
        $checks[] = $this->createCheck('Journalisation', 10, false, 'Activer les logs d\'erreurs et d\'audit.', 'https://cheatsheetseries.owasp.org/cheatsheets/Logging_Cheat_Sheet.html', 'Non évalué');
        $checks[] = $this->createCheck('Configuration sécurisée', 10, false, 'Désactiver les services inutiles.', 'https://owasp.org/www-project-secure-configuration/', 'Non évalué');
        $checks[] = $this->createCheck('Gestion des erreurs', 5, false, 'Cacher les informations sensibles.', 'https://cheatsheetseries.owasp.org/cheatsheets/Error_Handling_Cheat_Sheet.html', 'Non évalué');

        $level = $this->getEvaluationLevel($score);

        return [
            'score' => $score,
            'level' => $level,
            'color' => $this->getScoreColor($score),
            'checks' => $checks
        ];
    }

    public function getServerScore(Server $server): array
    {
        $scaData = $this->wazuhService->getScaScore($server->wazuh_agent_id ?? '');

        $score = $scaData['score'] ?? 0;
        $level = $scaData['level'] ?? 'Non évalué';
        $color = $scaData['color'] ?? 'var(--text-muted)';
        $source = $scaData ? 'Wazuh SCA' : 'Non configuré';

        return [
            'score' => $score,
            'level' => $level,
            'color' => $color,
            'source' => $source
        ];
    }

    private function createCheck($name, $weight, $is_passed, $remediation, $guideline, $status = null) {
        return [
            'name' => $name,
            'weight' => $weight,
            'status' => $status ?? ($is_passed ? 'Conforme' : 'Non conforme'),
            'is_passed' => $is_passed,
            'remediation' => $remediation,
            'guideline' => $guideline
        ];
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