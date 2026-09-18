<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AlertSettingController extends Controller
{
    // MF-183 : Règles d'alertes (Seuils)
    public function alertRules(): View
    {
        return view('layout.settings.alert-rules');
    }

    // MF-184 : Canaux de notification
    public function notifications(): View
    {
        return view('layout.settings.notifications');
    }

    // MF-168 : Fenêtres de maintenance
    public function maintenance(): View
    {
        return view('layout.settings.maintenance');
    }
}