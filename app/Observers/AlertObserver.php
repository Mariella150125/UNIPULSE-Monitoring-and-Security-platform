<?php

namespace App\Observers;

use App\Events\AlertCreated;
use App\Models\Alert;
use Illuminate\Support\Facades\Mail;

class AlertObserver
{
    public function created(Alert $alert)
    {
        // 1. On diffuse l'alerte à Reverb pour le temps réel (liste + cloche)
        broadcast(new AlertCreated([
            'id' => $alert->id,
            'title' => $alert->title,
            'description' => $alert->description,
            'priority' => $alert->priority,
            'source' => $alert->source,
            'code' => $alert->code ?? 'ALR-' . $alert->id
        ]))->toOthers();

        // 2. Envoi d'Email selon la matrice de notification
                // 2. Envoi d'Email selon la matrice de notification
        $priority = $alert->priority;
        $emailEnabled = \App\Models\Setting::get("{$priority}_email", '0') == '1';

        if ($emailEnabled) {
            try {
                // On récupère tous les administrateurs (ou tous les utilisateurs)
                $admins = \App\Models\User::all(); // Ajoutez ->where('role', 'admin') si vous avez un système de rôles
                $emails = $admins->pluck('email')->toArray();

                if (!empty($emails)) {
                    Mail::raw(
                        "🚨 ALERTE UNIPULSE\n\n" .
                        "Titre : {$alert->title}\n" .
                        "Priorité : " . ucfirst($priority) . "\n" .
                        "Description : {$alert->description}\n\n" .
                        "Connectez-vous à la plateforme pour traiter cette alerte.",
                        function ($message) use ($alert, $emails) {
                            $message->to($emails) // Envoie à TOUS les admins
                                    ->subject('[ALERTE UNIPULSE] ' . $alert->title);
                        }
                    );
                }
            } catch (\Exception $e) {
                // Si l'email échoue, on ne fait pas planter le système
            }
        }
    }
}