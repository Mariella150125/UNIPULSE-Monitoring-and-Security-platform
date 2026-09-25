@extends('layout.app')

@section('content')
<div class="page-title">
    <a href="{{ route('alerts.index') }}" class="btn btn-cancel">
        <i class="fa-solid fa-arrow-left"></i> Retour à la liste
    </a>
    <h1>Détail de l'Alerte {{ $alert->code ?? 'ALR-'.$alert->id }}</h1>
</div>

@if(session('success'))
    <div class="success-message" style="background: var(--sage-green); color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
@endif

<div class="grid-2">
    <!-- COLONNE GAUCHE : INFOS PRINCIPALES -->
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
            <p style="color: var(--text-muted); margin-bottom: 20px;">{{ $alert->description }}</p>

            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 20px 0;">

            <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
                <div>
                    <strong>État :</strong> 
                    @if($alert->status == 'open') <span style="color: var(--red); font-weight: bold;">Ouverte</span>
                    @elseif($alert->status == 'acknowledged') <span style="color: var(--orange); font-weight: bold;">Acquittée</span>
                    @elseif($alert->status == 'resolved') <span style="color: var(--sage-green); font-weight: bold;">Résolue</span>
                    @else <span style="color: var(--text-muted); font-weight: bold;">Fermée</span> @endif
                </div>
                <div><strong>Date :</strong> {{ $alert->created_at->format('d/m/Y H:i') }}</div>
            </div>

            <!-- MF-171 : LIEN RUNBOOK -->
            <div style="margin-top: 15px; padding: 10px; background: var(--bg-muted, #f5f5f5); border-radius: 5px;">
                <strong><i class="fa-solid fa-book"></i> Procédure de résolution (Runbook) :</strong><br>
                <a href="{{ $alert->runbook_url ?? '#' }}" target="_blank" style="color: var(--teal);">
                    Consulter la documentation pour résoudre cette alerte
                </a>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 10px; flex-wrap: wrap;">
                @if($alert->status == 'open')
                    <form action="{{ route('alerts.acknowledge', $alert->id) }}" method="POST">
                        @csrf @method('PUT')
                        <button type="submit" class="btn btn-primary" style="background: var(--sage-green);">
                            <i class="fa-solid fa-check"></i> Acquitter
                        </button>
                    </form>
                @endif

                @if($alert->status != 'closed')
                    <form action="{{ route('alerts.close', $alert->id) }}" method="POST">
                        @csrf @method('PUT')
                        <button type="submit" class="btn btn-danger">
                            <i class="fa-solid fa-xmark"></i> Fermer
                        </button>
                    </form>
                @endif

       
              
                @if($alert->status != 'closed' && $alert->status != 'snoozed')
                    <form action="{{ route('alerts.snooze', $alert->id) }}" method="POST" style="display: flex; gap: 5px; align-items: center;">
                        @csrf
                        @method('PUT')  {{-- C'EST OBLIGATOIRE POUR QUE LARAVEL ACCEPTE --}}
                        
                        <select name="minutes" class="form-control" style="width: 100px;">
                            <option value="15">15 min</option>
                            <option value="60">1 heure</option>
                            <option value="240">4 heures</option>
                        </select>
                        
                        <button type="submit" class="btn btn-cancel" title="Ignorer temporairement">
                            <i class="fa-solid fa-clock"></i> Ignorer
                        </button>
                    </form>
                @endif 
            </div>
        </div>
    </div>

    <!-- COLONNE DROITE : ASSIGNATION & COMMENTAIRES (MF-165) -->
    <div class="panel">
        <div class="panel-header">
            <p>Gestion & Historique</p>
        </div>
        <div style="padding: 24px;">
            <h4>Assigner à un responsable</h4>
            @if($alert->status != 'closed')
                <form action="{{ route('alerts.assign', $alert->id) }}" method="POST" style="display: flex; gap: 10px; align-items: center; margin-bottom: 30px;">
                    @csrf @method('PUT')
                    <select name="assigned_to" class="form-control" required>
                        <option value="" disabled selected>Choisir...</option>
                        @foreach(\App\Models\User::orderBy('name')->get() as $user)
                            <option value="{{ $user->id }}" {{ $alert->assigned_to == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i></button>
                </form>
            @else
                <p>Alerte fermée.</p>
            @endif

            <hr>

            <h4>Commentaires (MF-165)</h4>
            <div style="max-height: 300px; overflow-y: auto; border: 1px solid #eee; padding: 10px; margin-bottom: 15px;">
                @forelse($alert->comments as $comment)
                    <div style="margin-bottom: 10px; border-bottom: 1px dashed #eee; padding-bottom: 5px;">
                        <strong>{{ $comment->user->name ?? 'Système' }}</strong> 
                        <small style="float: right;">{{ $comment->created_at->diffForHumans() }}</small>
                        <p style="margin-top: 5px;">{{ $comment->comment }}</p>
                    </div>
                @empty
                    <p style="color: var(--text-muted); font-style: italic;">Aucun commentaire pour le moment.</p>
                @endforelse
            </div>

            @if($alert->status != 'closed')
                <form action="{{ route('alerts.comment', $alert->id) }}" method="POST">
                    @csrf
                    <textarea name="comment" class="form-control" rows="3" placeholder="Ajouter une note ou un commentaire..." required></textarea>
                    <button type="submit" class="btn btn-cancel" style="margin-top: 10px; width: 100%;">
                        <i class="fa-solid fa-paper-plane"></i> Ajouter
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection