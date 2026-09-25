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
        public function scanDependencies(string $appUrl, string $language = 'php'): ?array
    {
        // Liste des fichiers de dépendances à chercher, peu importe le langage
        $filesToTry = [
            'composer.lock' => 'Packagist',
            'package-lock.json' => 'npm',
            'requirements.txt' => 'PyPI'
        ];

        foreach ($filesToTry as $fileName => $ecosystem) {
            $fileUrl = rtrim($appUrl, '/') . '/' . $fileName;

            try {
                $response = Http::timeout(5)->withoutVerifying()->get($fileUrl);

                if (!$response->successful()) {
                    continue; // Fichier introuvable, on essaie le suivant
                }

                $lockData = $response->json();
                if (!$lockData) {
                    // Si c'est du texte (requirements.txt), on le lit différemment
                    $lockData = $response->body();
                }

                $packages = $lockData['packages'] ?? [];
                $vulnerabilities = [];
                $packagesToScan = array_slice($packages, 0, 15); // Limité pour ne pas dépasser le timeout

                foreach ($packagesToScan as $package) {
                    $name = $package['name'] ?? null;
                    $version = ltrim($package['version'] ?? '', 'v');
                    if (!$name || !$version) continue;

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

            // Si on a trouvé un fichier et l'a scanné, on renvoie le résultat (même si 0 failles)
            return $vulnerabilities;

            } catch (\Exception $e) {
                continue; // Erreur réseau, on essaie le fichier suivant
            }
        }

        // Si aucun fichier n'a été trouvé
        return null;
    }
}