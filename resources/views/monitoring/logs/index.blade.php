@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Logs Centralisés</h1>
    <p>Recherche et analyse des logs système et applicatifs .</p>
</div>

{{-- KPIs Rapides --}}
<div class="usr-kpi-row" style="margin-bottom: 24px;">
    <div class="kpi-card">
        <div class="kpi-icon c-red"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <p class="kpi-label">Erreurs Totales</p>
        <p class="kpi-value">{{ $stats['error'] }}</p>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon c-orange"><i class="fa-solid fa-circle-exclamation"></i></div>
        <p class="kpi-label">Avertissements</p>
        <p class="kpi-value">{{ $stats['warning'] }}</p>
    </div>
</div>

<div class="panel">
    {{-- BARRE DE RECHERCHE ET FILTRES (MF-32) --}}
    <form method="GET" action="{{ route('monitoring.logs.index') }}">
        <div class="panel-header" style="flex-wrap: wrap; gap: 10px;">
            <div class="search-bar" style="flex: 1; min-width: 200px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Recherche plein texte dans les logs...">
            </div>
            
            <div class="search-filter" style="gap: 10px;">
                <select name="level" class="filter-btn">
                    <option value="ALL" {{ request('level') == 'ALL' ? 'selected' : '' }}>Tous niveaux</option>
                    <option value="ERROR" {{ request('level') == 'ERROR' ? 'selected' : '' }}>Erreur</option>
                    <option value="WARNING" {{ request('level') == 'WARNING' ? 'selected' : '' }}>Warning</option>
                    <option value="INFO" {{ request('level') == 'INFO' ? 'selected' : '' }}>Info</option>
                    <option value="CRITICAL" {{ request('level') == 'CRITICAL' ? 'selected' : '' }}>Critique</option>
                </select>
                
                <input type="text" name="source" value="{{ request('source') }}" placeholder="Source (Nginx, MySQL...)" class="filter-btn" style="width: 120px;">
                
                <button type="submit" class="usr-btn-1" style="padding: 8px 15px;">
                    <i class="fa-solid fa-filter"></i> Filtrer
                </button>
            </div>
        </div>
    </form>

    {{-- TABLEAU DES LOGS --}}
    <div style="background: #1a1e2e; color: #c8ccd4; font-family: 'Courier New', monospace; font-size: 13px; border-radius: 0 0 24px 24px; overflow: auto; max-height: 600px;">
        @forelse ($logs as $log)
            <div style="padding: 10px 20px; border-bottom: 1px solid #2a2e3e; display: flex; gap: 15px; align-items: flex-start;">
                <span style="color: #8a9490; white-space: nowrap;">{{ $log->created_at->format('Y-m-d H:i:s') }}</span>
                
                @if($log->level == 'ERROR' || $log->level == 'CRITICAL')
                    <span style="color: #ff4d4f; font-weight: bold; width: 70px;">[{{ $log->level }}]</span>
                @elseif($log->level == 'WARNING')
                    <span style="color: #ffa940; font-weight: bold; width: 70px;">[{{ $log->level }}]</span>
                @else
                    <span style="color: #52c41a; font-weight: bold; width: 70px;">[{{ $log->level }}]</span>
                @endif

                <span style="color: #82aaff; width: 100px; white-space: nowrap;">{{ $log->source }}</span>
                
                <span style="flex: 1; white-space: pre-wrap;">{{ $log->message }}</span>
                
                @if($log->application)
                    <span style="color: #c792ea; font-size: 11px;">({{ $log->application->name }})</span>
                @endif
            </div>
        @empty
            <div style="padding: 40px; text-align: center; color: #8a9490;">
                <i class="fa-solid fa-file-lines" style="font-size: 24px; margin-bottom: 10px;"></i><br>
                Aucun log trouvé. Les journaux apparaîtront ici une fois reçus via Wazuh ou les Webhooks.
            </div>
        @endforelse
    </div>
    {{-- PAGINATION SOMBRE NUMÉROTÉE --}}
    <div style="padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #2a2e3e; background: #1a1e2e; border-radius: 0 0 24px 24px; flex-wrap: wrap; gap: 15px;">
        
        {{-- Infos sur le nombre de logs --}}
        <span style="font-size: 13px; color: #8a9490; font-family: 'Courier New', monospace;">
            Page {{ $logs->currentPage() }} sur {{ $logs->lastPage() }} ({{ $logs->total() }} logs)
        </span>

        {{-- Boutons Numérotés --}}
        <div style="display: flex; align-items: center; gap: 6px;">
            {{-- Bouton Précédent --}}
            @if ($logs->onFirstPage())
                <button disabled style="background: #2a2e3e; border: 1px solid #3a3e4e; color: #555; cursor: not-allowed; padding: 6px 12px; border-radius: 6px; font-family: 'Courier New', monospace;">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
            @else
                <a href="{{ $logs->appends(request()->query())->previousPageUrl() }}" style="background: #2a2e3e; border: 1px solid #3a3e4e; color: #c8ccd4; text-decoration: none; padding: 6px 12px; border-radius: 6px; font-family: 'Courier New', monospace;">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            @endif

            {{-- Numéros de page (Boucle intelligente pour afficher des "..." si trop de pages) --}}
            @for ($i = 1; $i <= $logs->lastPage(); $i++)
                @if ($i == $logs->currentPage())
                    <span style="background: #1d4a40; color: #fff; padding: 6px 12px; border-radius: 6px; font-family: 'Courier New', monospace; font-size: 13px; font-weight: bold;">
                        {{ $i }}
                    </span>
                @elseif (abs($i - $logs->currentPage()) <= 2 || $i == 1 || $i == $logs->lastPage())
                    <a href="{{ $logs->appends(request()->query())->url($i) }}" style="background: #2a2e3e; border: 1px solid #3a3e4e; color: #c8ccd4; text-decoration: none; padding: 6px 12px; border-radius: 6px; font-family: 'Courier New', monospace; font-size: 13px;">
                        {{ $i }}
                    </a>
                @elseif (abs($i - $logs->currentPage()) == 3)
                    <span style="color: #555; padding: 6px 4px; font-family: 'Courier New', monospace;">...</span>
                @endif
            @endfor

            {{-- Bouton Suivant --}}
            @if ($logs->hasMorePages())
                <a href="{{ $logs->appends(request()->query())->nextPageUrl() }}" style="background: #2a2e3e; border: 1px solid #3a3e4e; color: #c8ccd4; text-decoration: none; padding: 6px 12px; border-radius: 6px; font-family: 'Courier New', monospace;">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            @else
                <button disabled style="background: #2a2e3e; border: 1px solid #3a3e4e; color: #555; cursor: not-allowed; padding: 6px 12px; border-radius: 6px; font-family: 'Courier New', monospace;">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            @endif
        </div>
    </div>
    
</div>

@endsection