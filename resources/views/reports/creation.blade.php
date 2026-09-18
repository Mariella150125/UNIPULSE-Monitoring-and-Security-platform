@extends('layout.app')

@section('content')
<div class="page-title">
    <a href="{{ route('reports.index') }}" class="btn btn-cancel">
        <i class="fa-solid fa-arrow-left"></i> Retour à l'historique
    </a>
    <h1>Créer un nouveau rapport</h1>
</div>

@if(session('success'))
    <div class="success-message">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
@endif

<div class="panel">
    <form action="{{ route('reports.store') }}" method="POST">
        @csrf
        
        <div class="settings-form-grid">
            <div class="form-group">
                <label>Type de rapport (MF-174)</label>
                <select name="type" class="form-control" required>
                    <option value="general">Rapport Général</option>
                    <option value="application">Rapport Application</option>
                    <option value="server">Rapport Serveur</option>
                    <option value="security">Sécurité & Conformité</option>
                    <option value="alert">Rapport d'Alertes</option>
                    <option value="executive">Rapport Exécutif (Direction)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Format du fichier (MF-176)</label>
                <select name="format" class="form-control" required>
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel</option>
                    <option value="word">Word</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-file-circle-plus"></i> Générer le rapport
            </button>
        </div>
    </form>
</div>
@endsection