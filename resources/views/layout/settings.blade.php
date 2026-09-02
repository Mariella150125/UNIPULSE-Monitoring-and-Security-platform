@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Settings</h1>
    <p>Configuration de la plateforme</p>
</div>

<div class="panel">
    <div class="settings-grid">
        
        <a href="{{ route('application-types.index') }}" class="settings-card">
            <div class="settings-card-icon">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
                <h3>Types d'applications</h3>
                <p>Ajouter et gérer les types d'applications disponibles dans la plateforme.</p>
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

        <div class="settings-card">
            <div class="settings-card-icon">
                <i class="fa-solid fa-plug"></i>
            </div>
            <div>
                <h3>Connecteurs</h3>
                <p>Configurer Prometheus et Wazuh.</p>
            </div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </div>

        <a href="{{ route('settings.platform.index') }}" class="settings-card">
            <div class="settings-card-icon">
                <i class="fa-solid fa-sliders"></i>
            </div>
            <div>
                <h3>Paramètres de la plateforme</h3>
                <p>Seuils d'alertes, notifications, sécurité et journalisation.</p>
            </div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>

        <a href="{{ route('settings.audit-logs.index') }}" class="settings-card">
            <div class="settings-card-icon">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div>
                <h3>Journaux d'audit</h3>
                <p>Consulter toutes les actions d'administration et tentatives d'authentification.</p>
            </div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>

    </div>
    <a href="{{ route('server.index') }}" class="btn btn-cancel">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
</div>

@endsection