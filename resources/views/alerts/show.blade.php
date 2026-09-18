@extends('layout.app')

@section('content')
<div class="page-title">
    <a href="{{ route('alerts.index') }}" class="btn btn-cancel">
        <i class="fa-solid fa-arrow-left"></i> Retour à la liste
    </a>
    <h1>Détail de l'Alerte {{ $alert->code ?? 'ALR-'.$alert->id }}</h1>
</div>

@if(session('success'))
    <div class="success-message">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
@endif

<div class="panel">
    <div class="panel-header">
        <p>Informations de l'alerte</p>
    </div>
    <div style="padding: 24px;">
        <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 20px;">
            @if($alert->priority == 'critical')
                <span class="badge badge-critical" style="font-size: 16px; padding: 8px 16px;">CRITIQUE</span>
            @elseif($alert->priority == 'high')
                <span class="badge badge-major" style="font-size: 16px; padding: 8px 16px;">HAUTE</span>
            @else
                <span class="badge" style="font-size: 16px; padding: 8px 16px;">MOYENNE</span>
            @endif
            <span style="font-size: 14px; color: var(--text-muted);">Source : {{ strtoupper($alert->source) }}</span>
        </div>

        <h2>{{ $alert->title }}</h2>
        <p style="color: var(--text-muted); margin-bottom: 20px;">
            {{ $alert->description }}
        </p>

        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 20px 0;">

        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <div>
                <strong>État :</strong> 
                @if($alert->status == 'open') <span style="color: var(--red); font-weight: bold;">Ouverte</span>
                @elseif($alert->status == 'acknowledged') <span style="color: var(--orange); font-weight: bold;">Acquittée</span>
                @elseif($alert->status == 'resolved') <span style="color: var(--sage-green); font-weight: bold;">Résolue</span>
                @else <span style="color: var(--text-muted); font-weight: bold;">Fermée</span> @endif
            </div>
            <div>
                <strong>Date :</strong> {{ $alert->created_at->format('d/m/Y H:i') }}
            </div>
        </div>

        <div style="margin-top: 30px; display: flex; gap: 10px; flex-wrap: wrap;">
            
            {{-- BOUTON ACQUITTER --}}
            @if($alert->status == 'open')
                <form action="{{ route('alerts.acknowledge', $alert->id) }}" method="POST">
                    @csrf @method('PUT')
                    <button type="submit" class="btn btn-primary" style="background: var(--sage-green);" onclick="stopUrgentAlert()">
                        <i class="fa-solid fa-check"></i> Acquitter l'alerte
                    </button>
                </form>
            @endif

            {{-- BOUTON FERMER --}}
            @if($alert->status != 'closed')
                <form action="{{ route('alerts.close', $alert->id) }}" method="POST">
                    @csrf @method('PUT')
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-xmark"></i> Fermer l'alerte
                    </button>
                </form>
            @endif

            {{-- FORMULAIRE ASSIGNER --}}
            @if($alert->status != 'closed')
                <form action="{{ route('alerts.assign', $alert->id) }}" method="POST" style="display: flex; gap: 10px; align-items: center;">
                    @csrf @method('PUT')
                    <select name="assigned_to" class="form-control" style="width: 200px;" required>
                        <option value="" disabled selected>Assigner à...</option>
                        @foreach(\App\Models\User::orderBy('name')->get() as $user)
                            <option value="{{ $user->id }}" {{ $alert->assigned_to == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-cancel">
                        <i class="fa-solid fa-user-plus"></i> Assigner
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection