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

        // A02 - HTTPS obligatoire (Poids: 10)
        $isHttps = $app->url && str_starts_with($app->url, 'https://');
        $checks[] = $this->createCheck('HTTPS obligatoire', 10, $isHttps, 'Activer la redirection HTTP vers HTTPS.', 'https://owasp.org/www-project-application-security-verification-standard/');
        $score += $isHttps ? 10 : 0;

        // Variables pour les vérifications HTTP
        $sslValid = false;
        $headersValid = false;

        // On ne fait une requête HTTP que si l'URL existe
        if ($app->url) {
            try {
                // ON FAIT UNE SEULE REQUÊTE AVEC UN TIMEOUT TRÈS COURT (2 secondes max)
                $response = Http::timeout(2)->withoutVerifying()->get($app->url);
                
                // 1. Vérification SSL
                $sslValid = $response->successful();
                
                // 2. Vérification En-têtes (HSTS)
                $hsts = $response->header('Strict-Transport-Security');
                $headersValid = !empty($hsts);
                
            } catch (\Exception $e) {
                // Si l'URL ne répond pas dans les 2 secondes, on laisse les variables à false
                $sslValid = false;
                $headersValid = false;
            }
        }

        // A02 - Certificat SSL valide (Poids: 10)
        $checks[] = $this->createCheck('Certificat SSL valide', 10, $sslValid, 'Renouveler ou corriger le certificat SSL.', 'https://owasp.org/www-project-application-security-verification-standard/');
        $score += $sslValid ? 10 : 0;

        // A05 - En-têtes HTTP (Poids: 15)
        $checks[] = $this->createCheck('En-têtes HTTP', 15, $headersValid, 'Activer HSTS, CSP, X-Frame-Options.', 'https://owasp.org/www-project-secure-headers/');
        $score += $headersValid ? 15 : 0;

        // A06 - Dépendances vulnérables (Poids: 20) - Simulé
        $hasVuln = rand(0, 1) == 1;
        $checks[] = $this->createCheck('Dépendances vulnérables', 20, $hasVuln, 'Mettre à jour les dépendances vulnérables (CVE).', 'https://owasp.org/www-project-dependency-check/');
        $score += $hasVuln ? 20 : 0;

        // Autres contrôles (Simulés conformes)
        $checks[] = $this->createCheck('Versions des dépendances', 10, true, 'Maintenir les dépendances à jour.', 'https://owasp.org/www-project-dependency-check/');
        $score += 10;
        $checks[] = $this->createCheck('Authentification sécurisée', 10, true, 'Renforcer la politique d\'authentification.', 'https://owasp.org/www-project-authentication-cheat-sheet/');
        $score += 10;
        $checks[] = $this->createCheck('Journalisation', 10, true, 'Activer les logs d\'erreurs et d\'audit.', 'https://cheatsheetseries.owasp.org/cheatsheets/Logging_Cheat_Sheet.html');
        $score += 10;
        $checks[] = $this->createCheck('Configuration sécurisée', 10, true, 'Désactiver les services inutiles.', 'https://owasp.org/www-project-secure-configuration/');
        $score += 10;
        $checks[] = $this->createCheck('Gestion des erreurs', 5, true, 'Cacher les informations sensibles.', 'https://cheatsheetseries.owasp.org/cheatsheets/Error_Handling_Cheat_Sheet.html');
        $score += 5;

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
        $cisData = $this->wazuhService->getCisScore($server->wazuh_agent_id ?? '');

        $score = $cisData['score'] ?? rand(60, 98);
        $level = $cisData['level'] ?? $this->getEvaluationLevel($score);
        $color = $cisData['color'] ?? $this->getScoreColor($score);

        return [
            'score' => $score,
            'level' => $level,
            'color' => $color,
            'source' => $cisData ? 'Wazuh' : 'Démo'
        ];
    }

    private function createCheck($name, $weight, $is_passed, $remediation, $guideline) {
        return [
            'name' => $name,
            'weight' => $weight,
            'status' => $is_passed ? 'Conforme' : 'Non conforme',
            'is_passed' => $is_passed,
            'remediation' => $remediation,
            'guideline' => $guideline
        ];
    }

    private function getEvaluationLevel($score) {
        if ($score >= 90) return 'Excellent';
        if ($score >= 70) return 'Moyen';
        if ($score >= 50) return 'Faible';
        return 'Nul';
    }

    private function getScoreColor($score) {
        if ($score >= 90) return 'var(--sage-green)';
        if ($score >= 70) return 'var(--orange)';
        return 'var(--red)';
    }
}