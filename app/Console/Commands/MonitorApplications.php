<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\Application;
use App\Models\ApplicationAvailability;
use App\Models\MaintenanceWindow;
use App\Models\Server;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class MonitorApplications extends Command
{
    protected $signature = 'monitor:run';
    protected $description = 'Vérifie la disponibilité des applications et met à jour le statut des serveurs via Prometheus';

    public function handle()
    {
        $this->info("Début du cycle de monitoring...");

        // --- 1. VÉRIFICATION DES APPLICATIONS ---
        $apps = Application::where('status', 'active')->get();
        $this->info("Vérification de {$apps->count()} applications...");

        foreach ($apps as $app) {
            if (!$app->url) continue;

            // Vérifier si l'application est en maintenance
            $inMaintenance = MaintenanceWindow::where('resource_type', 'application')
                ->where('resource_id', $app->id)
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->exists();

            if ($inMaintenance) {
                $this->info("Application {$app->name} en maintenance, vérification suspendue.");
                continue;
            }

            $isAvailable = false;
            $responseTime = 0;

            try {
                $start = microtime(true);
                $response = Http::timeout(10)->withoutVerifying()->get($app->url);
                $responseTime = round((microtime(true) - $start) * 1000);
                $isAvailable = $response->successful();
            } catch (\Exception $e) {
                $isAvailable = false;
            }

            ApplicationAvailability::create([
                'application_id' => $app->id,
                'is_available' => $isAvailable,
                'response_time' => $responseTime,
                'checked_at' => now(),
            ]);

            if (!$isAvailable) {
                $existingAlert = Alert::where('title', 'Application Injoignable : ' . $app->name)
                    ->whereNotIn('status', ['resolved', 'closed'])
                    ->first();

                if (!$existingAlert) {
                    Alert::create([
                        'title' => 'Application Injoignable : ' . $app->name,
                        'status' => 'open',
                        'description' => 'L\'application ne répond pas sur l\'URL ' . $app->url,
                        'source' => 'health-check',
                        'priority' => 'critical',
                        'code' => 'HC-' . $app->id . '-' . now()->format('His') 
                    ]);
                }
                $this->error("{$app->name} est INJOIGNABLE.");
            } else {
                // Vérifier les seuils d'alerte (Temps de réponse)
                $rtCritical = (int) \App\Models\Setting::get('rt_critical', 3000);
                if ($responseTime > $rtCritical) {
                    Alert::firstOrCreate(
                        ['title' => 'Temps de réponse critique : ' . $app->name, 'status' => 'open'],
                        [
                            'description' => 'Temps de réponse : ' . $responseTime . 'ms (Seuil critique : ' . $rtCritical . 'ms)',
                            'source' => 'health-check',
                            'priority' => 'critical',
                            'code' => 'RT-' . $app->id
                        ]
                    );
                    $this->warn("{$app->name} a un temps de réponse critique ({$responseTime}ms).");
                }
                $this->info("{$app->name} est OK ({$responseTime}ms).");
            }

            // --- VÉRIFICATION DU CERTIFICAT SSL (MF-112) ---
            if ($app->url && str_starts_with($app->url, 'https://')) {
                $parsedUrl = parse_url($app->url);
                $host = $parsedUrl['host'] ?? null;
                $port = $parsedUrl['port'] ?? 443;
                
                if ($host) {
                    $context = stream_context_create(["ssl" => ["capture_peer_cert" => true]]);
                    $stream = @stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, 3, STREAM_CLIENT_CONNECT, $context);
                    
                    if ($stream) {
                        $certParams = stream_context_get_params($stream);
                        $peerCert = $certParams['options']['ssl']['peer_certificate'] ?? null;
                        
                        if ($peerCert) {
                            $cert = openssl_x509_parse($peerCert);
                            if ($cert && isset($cert['validTo_time_t'])) {
                                $daysLeft = round(($cert['validTo_time_t'] - time()) / 86400);
                                
                                // Si le certificat expire dans moins de 30 jours
                                if ($daysLeft <= 30) {
                                    
                                    // Détermine la priorité : critique si <= 10 jours, sinon haute
                                    $priority = ($daysLeft <= 10) ? 'critical' : 'high';
                                    $alertTitle = 'Certificat SSL : ' . $app->name;

                                    $existingSslAlert = Alert::where('title', $alertTitle)
                                        ->whereNotIn('status', ['resolved', 'closed'])
                                        ->first();

                                    if (!$existingSslAlert) {
                                        $description = $daysLeft < 0 
                                            ? "Le certificat SSL a expiré il y a " . abs($daysLeft) . " jours !" 
                                            : "Le certificat SSL expire dans {$daysLeft} jours.";

                                        Alert::create([
                                            'title' => $alertTitle,
                                            'status' => 'open',
                                            'description' => $description,
                                            'source' => 'health-check',
                                            'priority' => $priority,
                                            'code' => 'SSL-' . $app->id . '-' . now()->format('His')
                                        ]);
                                        
                                        if ($priority === 'critical') {
                                            $this->error("{$app->name} : Certificat SSL CRITIQUE (J-{$daysLeft}) !");
                                        } else {
                                            $this->warn("{$app->name} : Certificat SSL expire dans {$daysLeft} jours.");
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            // -----------------------------------------------------------
        }

        // --- 2. MISE À JOUR DU STATUT DES SERVEURS (Prometheus) ---
        $servers = Server::all();
        $prometheusService = app(\App\Services\PrometheusService::class);
        $this->info("Vérification de {$servers->count()} serveurs...");

        foreach ($servers as $server) {
            if (!$server->prometheus_instance) {
                $this->line("Serveur {$server->name}: Instance Prometheus non configurée, ignoré.");
                continue;
            }

            // Vérifier si le serveur est en maintenance
            $inServerMaintenance = MaintenanceWindow::where('resource_type', 'server')
                ->where('resource_id', $server->id)
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->exists();

            if ($inServerMaintenance) {
                if ($server->global_status !== 'maintenance') {
                    $server->update(['global_status' => 'maintenance']);
                    $this->info("Serveur {$server->name} mis en maintenance.");
                }
                continue;
            }

            $status = $prometheusService->getServerStatus($server->prometheus_instance);
            $isUp = ($status !== null && (float) $status >= 1.0);

            if (!$isUp) {
                $newStatus = 'offline';
            } else {
                // On récupère les métriques
                $cpuUsage = $prometheusService->getCpuUsage($server->prometheus_instance);
                $ramUsage = $prometheusService->getMemoryUsage($server->prometheus_instance);
                $diskUsage = $prometheusService->getDiskUsage($server->prometheus_instance);
                
                // On lit les seuils depuis les paramètres
                $cpuCritical = (float) \App\Models\Setting::get('cpu_critical', 90);
                $cpuWarning = (float) \App\Models\Setting::get('cpu_warning', 75);
                $ramCritical = (float) \App\Models\Setting::get('ram_critical', 90);
                $ramWarning = (float) \App\Models\Setting::get('ram_warning', 80);
                $diskCritical = (float) \App\Models\Setting::get('disk_critical', 90);
                $diskWarning = (float) \App\Models\Setting::get('disk_warning', 80);

                $newStatus = 'healthy';

                // Vérification CPU
                if ($cpuUsage !== null && $cpuUsage >= $cpuCritical) { $newStatus = 'critical'; }
                elseif ($cpuUsage !== null && $cpuUsage >= $cpuWarning) { $newStatus = 'warning'; }

                // Vérification RAM
                if ($newStatus === 'healthy' || $newStatus === 'warning') {
                    if ($ramUsage !== null && $ramUsage >= $ramCritical) { $newStatus = 'critical'; }
                    elseif ($ramUsage !== null && $ramUsage >= $ramWarning && $newStatus === 'healthy') { $newStatus = 'warning'; }
                }

                // Vérification DISK
                if ($newStatus === 'healthy' || $newStatus === 'warning') {
                    if ($diskUsage !== null && $diskUsage >= $diskCritical) { $newStatus = 'critical'; }
                    elseif ($diskUsage !== null && $diskUsage >= $diskWarning && $newStatus === 'healthy') { $newStatus = 'warning'; }
                }
            }
            
            if ($server->global_status !== $newStatus) {
                $server->update(['global_status' => $newStatus]);
                $this->info("Serveur {$server->name}: Statut mis à jour vers {$newStatus}.");

                // --- ALERTE SI LE SERVEUR DEVIENT HORS LIGNE OU CRITIQUE ---
                if ($newStatus === 'offline' || $newStatus === 'critical') {
                    $alertTitle = 'Serveur ' . ($newStatus === 'offline' ? 'Hors ligne' : 'Critique') . ' : ' . $server->name;
                    
                    $existingAlert = Alert::where('title', $alertTitle)
                        ->whereNotIn('status', ['resolved', 'closed'])
                        ->first();

                    if (!$existingAlert) {
                        Alert::create([
                            'title' => $alertTitle,
                            'status' => 'open',
                            'description' => 'Le serveur est ' . $newStatus . '. Intervention requise immédiate.',
                            'source' => 'prometheus',
                            'priority' => 'critical',
                            'code' => 'SRV-' . $server->id . '-' . now()->format('His')
                        ]);
                        $this->error("Alerte créée pour le serveur {$server->name} !");
                    }
                }
            } else {
                $this->line("Serveur {$server->name}: Statut inchangé ({$newStatus}).");
            }
        }

        $this->info("Cycle de monitoring terminé.");
    }
}