<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ScaScannerService
{
    /**
     * Interroge l'URL de l'application pour télécharger son fichier de dépendances
     * et l'analyse via l'API OSV.
     *
     * @param string $appUrl L'URL de l'application (ex: https://mon-app.com)
     * @param string $language Le langage (php, laravel, node.js, etc.)
     * @return array
     */
    public function scanDependencies(string $appUrl, string $language = 'php'): array
    {
        // Détermination du fichier et de l'écosystème
        $ecosystem = (strtolower($language) === 'php' || strtolower($language) === 'laravel') ? 'Packagist' : 'npm';
        $fileName = $ecosystem === 'Packagist' ? 'composer.lock' : 'package-lock.json';
        
        // On construit l'URL du fichier de dépendances
        $fileUrl = rtrim($appUrl, '/') . '/' . $fileName;

        try {
            // On tente de télécharger le fichier
            $response = Http::timeout(5)->withoutVerifying()->get($fileUrl);

            // Si le fichier est bloqué (403, 404) ou inaccessible
            if (!$response->successful()) {
                return []; // Aucune faille trouvée car fichier inaccessible
            }

            $lockData = $response->json();
            if (!$lockData) return [];

            $packages = $lockData['packages'] ?? [];
            $vulnerabilities = [];

            // On limite à 15 packages pour ne pas dépasser le timeout de la page
            $packagesToScan = array_slice($packages, 0, 15);

            foreach ($packagesToScan as $package) {
                $name = $package['name'] ?? null;
                $version = ltrim($package['version'] ?? '', 'v');

                if (!$name || !$version) continue;

                // Requête vers l'API officielle OSV
                $osvResponse = Http::timeout(3)->post('https://api.osv.dev/v1/query', [
                    'package' => [
                        'name' => $name,
                        'ecosystem' => $ecosystem
                    ],
                    'version' => $version
                ]);

                if ($osvResponse->successful()) {
                    $vulns = $osvResponse->json('vulns') ?? [];
                    
                    foreach ($vulns as $vuln) {
                        $cvssScore = 0;
                        if (isset($vuln['severity'])) {
                            foreach ($vuln['severity'] as $sev) {
                                if (str_contains($sev['score'], 'CVSS:3')) {
                                    preg_match('/CVSS:3.[0-9]\/AV:.*\/([0-9.]+)/', $sev['score'], $matches);
                                    $cvssScore = isset($matches[1]) ? (float)$matches[1] : 0;
                                }
                            }
                        }

                        $vulnerabilities[] = [
                            'cve'          => $vuln['id'] ?? 'N/A',
                            'cvss'         => $cvssScore,
                            'severity'     => $cvssScore >= 7.0 ? 'HIGH' : ($cvssScore >= 4.0 ? 'MEDIUM' : 'LOW'),
                            'dependency'   => $name . ' ' . $version,
                            'description'  => $vuln['summary'] ?? 'Vulnerability detected',
                            'published_at' => substr($vuln['published'] ?? now()->format('Y-m-d'), 0, 10),
                            'link'         => 'https://osv.dev/vulnerability/' . $vuln['id']
                        ];
                    }
                }
            }

            return $vulnerabilities;

        } catch (\Exception $e) {
            // En cas de timeout ou erreur réseau
            return [];
        }
    }
}