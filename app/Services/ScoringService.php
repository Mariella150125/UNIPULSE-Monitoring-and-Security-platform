<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Server;
use Illuminate\Support\Facades\Http;

class ScoringService
{
    protected $wazuhService;
    protected $scaService;

    public function __construct(WazuhService $wazuhService, ScaScannerService $scaService)
    {
        $this->wazuhService = $wazuhService;
        $this->scaService = $scaService;
    }

    public function getAppScore(Application $app): array
    {
        set_time_limit(60); // Autorise 60s par application
        $score = 0;
        $checks = [];

        // 1. HTTPS obligatoire (Poids: 10)
        $isHttps = $app->url && str_starts_with($app->url, 'https://');
        $checks[] = $this->createCheck('HTTPS obligatoire', 10, $isHttps, 'Activer la redirection HTTP vers HTTPS.', 'https://cheatsheetseries.owasp.org/cheatsheets/Transport_Layer_Protection_Cheat_Sheet.html', 'A02: Cryptographic Failures');
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
                    $context = stream_context_create([
                        "ssl" => [
                            "capture_peer_cert" => true,
                            "verify_peer" => false,
                            "verify_peer_name" => false,
                        ]
                    ]);
                    
                    $stream = @stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
                    
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
        $checks[] = $this->createCheck('Disponibilité HTTPS', 10, $isAvailable, 'Vérifier que l\'application répond en HTTP 200.', 'https://cheatsheetseries.owasp.org/cheatsheets/HTTP_Headers_Cheat_Sheet.html', 'A05: Security Misconfiguration');
        $score += $isAvailable ? 10 : 0;

        // Contrôle Certificat SSL (Poids: 10)
        $checks[] = $this->createCheck('Certificat SSL valide', 10, $sslValid, 'Renouveler ou corriger le certificat SSL (non expiré).', 'https://cheatsheetseries.owasp.org/cheatsheets/Transport_Layer_Protection_Cheat_Sheet.html', 'A02: Cryptographic Failures');
        $score += $sslValid ? 10 : 0;

        // Contrôle En-têtes HTTP (Poids: 15)
        $headersStatus = $headersValid ? 'Conforme' : 'Non conforme';
        if (!empty($headersDetails)) {
            $missing = array_keys(array_filter($headersDetails, function($v) { return !$v; }));
            if (!empty($missing)) $headersStatus .= ' (Manquants: ' . implode(', ', $missing) . ')';
        }
        $checks[] = $this->createCheck('En-têtes de sécurité (HSTS, CSP, X-Frame)', 15, $headersValid, 'Activer les en-têtes de sécurité essentiels.', 'https://cheatsheetseries.owasp.org/cheatsheets/HTTP_Headers_Cheat_Sheet.html', 'A05: Security Misconfiguration', $headersStatus);
        $score += $headersValid ? 15 : 0;

        // Contrôle Dépendances vulnérables (Vraie analyse SCA via l'API OSV)
        $isDepsSecure = false;
        $depsStatus = 'Non évalué';
        
        if ($app->url) {
            $scaVulns = $this->scaService->scanDependencies($app->url, $app->language ?? 'php');
            
            if ($scaVulns === null) {
                // Le scanner n'a pas trouvé le fichier lock (composer.lock / package-lock.json)
                $isDepsSecure = false;
                $depsStatus = 'Non conforme (Dépendances non surveillées / fichier introuvable)';
            } elseif (empty($scaVulns)) {
                // Le scanner a trouvé le fichier et il n'y a pas de failles
                $isDepsSecure = true;
                $depsStatus = 'Conforme (Aucune faille détectée)';
            } else {
                // Le scanner a trouvé le fichier et il y a des failles
                $isDepsSecure = false;
                $depsStatus = 'Non conforme (' . count($scaVulns) . ' vulnérabilités)';
            }
        }
        // ATTENTION : Le nom 'Dépendances vulnérables' doit correspondre exactement à ce qu'on cherche dans la vue Blade
        $checks[] = $this->createCheck('Dépendances vulnérables', 20, $isDepsSecure, 'Mettre à jour les dépendances vulnérables (CVE) ou exposer le fichier de lock pour le scan.', 'https://owasp.org/www-project-dependency-check/', 'A06: Vulnerable and Outdated Components', $depsStatus);
        $score += $isDepsSecure ? 20 : 0;
        
        // Autres contrôles (Non évalués)
        $checks[] = $this->createCheck('Versions des dépendances', 10, false, 'Maintenir les dépendances à jour.', 'https://owasp.org/www-project-dependency-check/', 'A06: Vulnerable and Outdated Components', 'Non évalué');
        $checks[] = $this->createCheck('Authentification sécurisée', 10, false, 'Renforcer la politique d\'authentification.', 'https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html', 'A07: Identification and Authentication Failures', 'Non évalué');
        $checks[] = $this->createCheck('Journalisation', 10, false, 'Activer les logs d\'erreurs et d\'audit.', 'https://cheatsheetseries.owasp.org/cheatsheets/Logging_Cheat_Sheet.html', 'A09: Security Logging and Monitoring Failures', 'Non évalué');
        $checks[] = $this->createCheck('Configuration sécurisée', 10, false, 'Désactiver les services inutiles.', 'https://cheatsheetseries.owasp.org/cheatsheets/Configuration_Cheat_Sheet.html', 'A05: Security Misconfiguration', 'Non évalué');
        $checks[] = $this->createCheck('Gestion des erreurs', 5, false, 'Cacher les informations sensibles.', 'https://cheatsheetseries.owasp.org/cheatsheets/Error_Handling_Cheat_Sheet.html', 'A05: Security Misconfiguration', 'Non évalué');

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

    private function createCheck($name, $weight, $is_passed, $remediation, $guideline, $owasp = null, $status = null) {
        return [
            'name' => $name,
            'weight' => $weight,
            'status' => $status ?? ($is_passed ? 'Conforme' : 'Non conforme'),
            'is_passed' => $is_passed,
            'remediation' => $remediation,
            'guideline' => $guideline,
            'owasp' => $owasp
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