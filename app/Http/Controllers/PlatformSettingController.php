<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Traits\AuditsActivity;

class PlatformSettingController extends Controller
{
    use AuditsActivity;

    public function index()
    {
        // Utilise le Modèle Eloquent au lieu de DB::table
        $settings = Setting::pluck('value', 'key')->toArray();
        
        return view('layout.tabs', compact('settings'));
    }
    
    public function update(Request $request)
    {
        // On exclut les champs techniques et l'info d'onglet
        $data = $request->except('_token', '_method', 'active_tab');

        // 1. Gestion des checkboxes : 
        // Les checkboxes non cochées ne sont pas envoyées par le navigateur.
        // On force donc leur valeur à '0' si elles ne sont pas présentes dans la requête.
        $checkboxes = ['notif_email', 'notif_slack', 'notif_sms'];
        foreach ($checkboxes as $checkbox) {
            $data[$checkbox] = $request->has($checkbox) ? '1' : '0';
        }

        // 2. Mise à jour ou insertion via le Modèle Eloquent
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // 3. On log l'audit UNE SEULE FOIS après la boucle
        $this->logAudit(
            action: 'update_settings', 
            resourceType: 'PlatformSetting', 
            isSuccess: true,
            details: 'Mise à jour des paramètres onglet : ' . $request->input('active_tab', 'Non spécifié')
        );

        return redirect()->back()->with('success', 'Paramètres sauvegardés avec succès.');
    }
}