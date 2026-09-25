<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SettingController extends Controller
{
    // Affiche la page des règles d'alertes
    public function alertRules(): View
    {
        return view('layout.settings.alert-rules');
    }

    // Affiche la page des notifications
    public function notifications(): View
    {
        return view('layout.settings.notifications');
    }

    // Affiche la page des maintenances
    public function maintenance(): View
    {
        // Plus tard tu pourras récupérer les maintenances existantes ici
        return view('layout.settings.maintenance');
    }

    // LA FONCTION MAGIQUE QUI SAUVEGARDE TOUT
    public function update(Request $request): RedirectResponse
    {
        try {
            // On liste toutes les cases à cocher possibles
            $checkboxes = [
                'critical_popup', 'critical_email', 'critical_slack',
                'high_popup', 'high_email', 'high_slack',
                'med_popup', 'med_email', 'med_slack',
                'low_popup', 'low_email', 'low_slack',
                'wazuh_active', 'prom_active'
            ];

            // On force la valeur à '0' si la case n'a pas été cochée
            foreach ($checkboxes as $cb) {
                $request->merge([$cb => $request->has($cb) ? '1' : '0']);
            }

            $data = $request->except(['_token', '_method']);

            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                if (empty($value)) {
                    $value = '0'; 
                }
                Setting::set($key, $value);
            }

            return redirect()->route('settings')->with('success', 'Les paramètres ont été enregistrés avec succès.');

        } catch (\Exception $e) {
            return redirect()->route('settings')->with('error', 'Une erreur est survenue lors de l\'enregistrement : ' . $e->getMessage());
        }
    }
    // Afficher la page de paramétrage des connecteurs
    public function connectors(): View
    {
        return view('layout.settings.connectors');
    }

    public function index()
    {
        // Si vous avez des paramètres en base de données, vous pouvez les récupérer ici
        // Exemple : $settings = \App\Models\Setting::all();
        
        return view('layout.settings'); // Assurez-vous d'avoir cette vue
    }
}