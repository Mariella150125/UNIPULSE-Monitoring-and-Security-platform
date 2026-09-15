@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Settings</h1>
    <p>Configuration de la plateforme UNIPULSE</p>
</div>

<div class="panel">
    <div class="settings-grid">
        
        <a href="{{ route('application-types.index') }}" class="settings-card">
            <div class="settings-card-icon">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
                <h3>Types d'applications</h3>
                <p>Ajouter et gérer les types d'applications disponibles.</p>
            </div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>

        <a href="{{ route('application-groups.index') }}" class="settings-card">
            <div class="settings-card-icon">
                <i class="fa-solid fa-cubes"></i>
            </div>
            <div>
                <h3>Groupes d'applications</h3>
                <p>Organiser les applications par groupes.</p>
            </div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>

        <a href="{{ route('server-groups.index') }}" class="settings-card">
            <div class="settings-card-icon">
                <i class="fa-solid fa-server"></i>
            </div>
            <div>
                <h3>Groupes de serveurs</h3>
                <p>Organiser les serveurs par groupes.</p>
            </div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>

        <a href="{{ route('settings.connectors.index') }}" class="settings-card">
            <div class="settings-card-icon">
                <i class="fa-solid fa-plug"></i>
            </div>
            <div>
                <h3>Connecteurs</h3>
                <p>Configurer le comportement global de Prometheus et Wazuh.</p>
            </div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>

        {{-- NOUVEAU : RÈGLES D'ALERTES (MF-183) --}}
        <a href="{{ route('settings.alert-rules.index') }}" class="settings-card">
            <div class="settings-card-icon" style="background: rgba(192, 57, 43, 0.1); color: var(--red);">
                <i class="fa-solid fa-bell-concierge"></i>
            </div>
            <div>
                <h3>Règles d'alertes</h3>
                <p>Définir les seuils CPU, RAM, Disque et Temps de réponse.</p>
            </div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>

        {{-- NOUVEAU : CANAUX DE NOTIFICATION (MF-184) --}}
        <a href="{{ route('settings.notifications.index') }}" class="settings-card">
            <div class="settings-card-icon" style="background: rgba(86, 130, 94, 0.1); color: var(--sage-green);">
                <i class="fa-solid fa-paper-plane"></i>
            </div>
            <div>
                <h3>Canaux de notification</h3>
                <p>Configurer Son, Pop-up et Emails selon la priorité.</p>
            </div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>

        {{-- NOUVEAU : MAINTENANCE (MF-168) --}}
        <a href="{{ route('settings.maintenance.index') }}" class="settings-card">
            <div class="settings-card-icon" style="background: rgba(224, 142, 62, 0.1); color: var(--orange);">
                <i class="fa-solid fa-hammer"></i>
            </div>
            <div>
                <h3>Fenêtres de maintenance</h3>
                <p>Suspendre les alertes pour un serveur ou une application.</p>
            </div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>

        <a href="{{ route('settings.platform.index') }}" class="settings-card">
            <div class="settings-card-icon">
                <i class="fa-solid fa-sliders"></i>
            </div>
            <div>
                <h3>Paramètres de la plateforme</h3>
                <p>Sécurité et journalisation globale.</p>
            </div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>

        <a href="{{ route('settings.audit-logs.index') }}" class="settings-card">
            <div class="settings-card-icon">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div>
                <h3>Journaux d'audit</h3>
                <p>Consulter les actions d'administration.</p>
            </div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>

    </div>
    <a href="{{ route('server.index') }}" class="btn btn-cancel">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
</div>

@endsection