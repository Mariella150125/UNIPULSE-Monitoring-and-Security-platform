<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\Report;
use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\Application;
use App\Models\Server;
use App\Services\ScoringService;
use App\Services\WazuhService;
use App\Models\User;

class SendScheduledReports extends Command
{
    protected $signature = 'reports:send {frequency=daily}';
    protected $description = 'Génère et envoie les rapports planifiés par email';

    public function handle(ScoringService $scoringService, WazuhService $wazuhService)
    {
        $admins = \App\Models\User::all();
        $frequency = $this->argument('frequency');
        $recipients = $admins->pluck('email')->toArray();

        if (empty($recipients)) {
            $this->error("Aucun utilisateur trouvé pour envoyer le rapport.");
            return;
        }
        // 1. Récupération des vraies données
        $totalServers = Server::count();
        $totalApps = Application::count();
        $totalAlerts = Alert::whereNotIn('status', ['resolved', 'closed'])->count();
        
        // --- CALCUL DU VRAI SCORE DE SÉCURITÉ ---
        $appScores = [];
        foreach (Application::all() as $app) {
            $appScores[] = $scoringService->getAppScore($app)['score'];
        }
        $globalAppScore = count($appScores) > 0 ? round(array_sum($appScores) / count($appScores)) : 0;

        $wazuhConfigured = $wazuhService->isConfigured();
        $globalServerScore = null;
        
        if ($wazuhConfigured) {
            $serverScores = [];
            foreach (Server::all() as $server) {
                $serverScores[] = $scoringService->getServerScore($server)['score'];
            }
            $globalServerScore = count($serverScores) > 0 ? round(array_sum($serverScores) / count($serverScores)) : 0;
        }

        // Le score global n'est calculé que si on a les deux, sinon c'est le score partiel App
        $securityScore = $globalServerScore !== null 
            ? round(($globalAppScore + $globalServerScore) / 2) 
            : $globalAppScore;
        // -----------------------------------------
                // --- NOUVELLES DONNÉES POUR LE PDF ---
        $serverList = [];
        foreach (Server::take(10)->get() as $s) {
            $serverList[] = ['name' => $s->name, 'ip' => $s->ip_address, 'os' => $s->os, 'status' => $s->global_status ?? 'healthy'];
        }

        $appList = [];
        foreach (Application::take(10)->get() as $a) {
            $appList[] = ['name' => $a->name, 'url' => $a->url ?? '-', 'status' => $a->status ?? 'active'];
        }

        $appDetails = [];
        foreach (Application::take(10)->get() as $a) {
            $scoreData = $scoringService->getAppScore($a);
            $failures = [];
            foreach ($scoreData['checks'] as $check) {
                if (!$check['is_passed']) $failures[] = $check['name'];
            }
            $appDetails[] = ['name' => $a->name, 'score' => $scoreData['score'], 'failures' => $failures];
        }

        $alertList = [];
        foreach (Alert::latest()->take(5)->get() as $alt) {
            $alertList[] = [
                'title' => $alt->title, 
                'priority' => $alt->priority, 
                'status' => $alt->status, 
                'date' => $alt->created_at->format('d/m/Y H:i')
            ];
        }
        // -------------------------------------
        // 2. Préparation des données
        $data = [
            'title' => 'Rapport Général (' . ucfirst($frequency) . ')',
            'date' => now()->format('d/m/Y H:i'),
            'author' => 'Système Automatique UNIPULSE',
            'totalServers' => $totalServers,
            'totalApps' => $totalApps,
            'totalAlerts' => $totalAlerts,
            'securityScore' => $securityScore, 
            'serverList' => $serverList,
            'appList' => $appList,
            'appDetails' => $appDetails,
            'alertList' => $alertList,
            'stats' => [
                'Total Serveurs' => $totalServers,
                'Total Applications' => $totalApps,
                'Total Alertes Actives' => $totalAlerts,
                'Score Global Sécurité' => $securityScore . '%'
            ]
            
        ];

        // 3. Générer le PDF
        $pdf = Pdf::loadView('reports.pdf', $data);
        $fileName = 'rapport_auto_' . now()->format('d-m-Y') . '.pdf';
        $filePath = 'reports/' . $fileName;
        Storage::disk('public')->put($filePath, $pdf->output());

        // 4. Sauvegarder dans l'historique
         $systemUser = \App\Models\User::first(); 
        Report::create([
            'name' => $fileName,
            'type' => 'general',
            'format' => 'pdf',
            'file_path' => $filePath,
            'generated_by' => $systemUser ? $systemUser->id : null,
            'department' => 'Système',
        ]);

        // 5. Envoyer l'email
        Mail::send('emails.reports', ['data' => $data], function ($message) use ($recipients, $pdf, $fileName) {
            $message->to($recipients)
                    ->subject('Rapport Automatique UNIPULSE - ' . now()->format('d/m/Y'))
                    ->attachData($pdf->output(), $fileName, [
                        'mime' => 'application/pdf',
                    ]);
        });

        $this->info('Rapport ' . $frequency . ' envoyé avec succès ! (Score sécurité: ' . $securityScore . '%)');
    }
}