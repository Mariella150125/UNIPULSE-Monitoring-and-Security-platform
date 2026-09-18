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
        //dd($request->except(['_token', '_method']));
        try {
            $data = $request->except(['_token', '_method']);

            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value); // Si on a des tableaux (commes les scopes)
                }
                if (empty($value)) {
                    $value = '0'; 
                }
                Setting::set($key, $value);
            }

            return redirect()->back()->with('success', 'Les paramètres ont été enregistrés avec succès.');

        } catch (\Exception $e) {
            // Si Laravel plante pour une raison quelconque, on prévient l'utilisateur
            return redirect()->back()->with('error', 'Une erreur est survenue lors de l\'enregistrement : ' . $e->getMessage());
        }
    }
    // Afficher la page de paramétrage des connecteurs
    public function connectors(): View
    {
        return view('layout.settings.connectors');
    }

    // Si tu as besoin de sauvegarder ces paramètres, tu peux utiliser 
    // la fonction update() générique qu'on a créée précédemment, 
    // qui stocke tout dans la table `settings`.
}