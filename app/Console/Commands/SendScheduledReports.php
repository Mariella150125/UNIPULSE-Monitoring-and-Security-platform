<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\Report;
use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendScheduledReports extends Command
{
    protected $signature = 'reports:send {frequency=daily}';
    protected $description = 'Génère et envoie les rapports planifiés par email';

    public function handle()
    {
        $frequency = $this->argument('frequency');
        $recipients = ['direction@entreprise.com', 'admin@entreprise.com']; // Mets les vrais emails ici

        $data = [
            'title' => 'Rapport Général (' . ucfirst($frequency) . ')',
            'date' => now()->format('d/m/Y H:i'),
            'author' => 'Système Automatique UNIPULSE',
            'stats' => [
                'Total Alertes Actives' => Alert::whereNotIn('status', ['resolved', 'closed'])->count(),
                'Serveurs Critiques' => '2', // Remplace par de vraies requêtes
                'Score Global Sécurité' => '85%'
            ]
        ];

        // 1. Générer le PDF
        $pdf = Pdf::loadView('reports.templates.pdf', $data);
        $fileName = 'rapport_auto_' . now()->format('d-m-Y') . '.pdf';
        $filePath = 'reports/' . $fileName;
        Storage::disk('public')->put($filePath, $pdf->output());

        // 2. Sauvegarder dans l'historique
        Report::create([
            'name' => $fileName,
            'type' => 'general',
            'format' => 'pdf',
            'file_path' => $filePath,
            'generated_by' => 1, // ID de l'utilisateur système
            'department' => 'Système',
        ]);

        // 3. Envoyer l'email
        Mail::send('emails.report', ['data' => $data], function ($message) use ($recipients, $pdf, $fileName) {
            $message->to($recipients)
                    ->subject('Rapport Automatique UNIPULSE - ' . now()->format('d/m/Y'))
                    ->attachData($pdf->output(), $fileName, [
                        'mime' => 'application/pdf',
                    ]);
        });

        $this->info('Rapport ' . $frequency . ' envoyé avec succès !');
    }
}