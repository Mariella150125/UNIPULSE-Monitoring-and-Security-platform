@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Comparaison de Métriques </h1>
    <p>Comparez les performances de deux serveurs ou de deux applications sur une même période.</p>
</div>

<div class="panel">
    {{-- FORMULAIRE DE SÉLECTION --}}
        <form method="GET" action="{{ route('monitoring.compare.index') }}" class="search-filter-form">
        <div class="panel-header" style="flex-wrap: wrap; gap: 15px;">
            
            {{-- 1. Choisir le type --}}
            <select name="type" class="filter-btn" onchange="this.form.submit()">
                <option value="application" {{ $type == 'application' ? 'selected' : '' }}>Applications</option>
                <option value="server" {{ $type == 'server' ? 'selected' : '' }}>Serveurs</option>
            </select>

            {{-- 2. Choix de l'entité 1 --}}
            <select name="entity1" class="filter-btn" style="min-width: 200px;">
                <option value="">-- Sélectionner 1 --</option>
                @if($type == 'application')
                    @foreach($applications as $app)
                        <option value="{{ $app->id }}" {{ $entity1 == $app->id ? 'selected' : '' }}>{{ $app->name }}</option>
                    @endforeach
                @else
                    @foreach($servers as $server)
                        <option value="{{ $server->id }}" {{ $entity1 == $server->id ? 'selected' : '' }}>{{ $server->name }}</option>
                    @endforeach
                @endif
            </select>

            {{-- 3. Choix de l'entité 2 --}}
            <select name="entity2" class="filter-btn" style="min-width: 200px;">
                <option value="">-- Sélectionner 2 --</option>
                @if($type == 'application')
                    @foreach($applications as $app)
                        <option value="{{ $app->id }}" {{ $entity2 == $app->id ? 'selected' : '' }}>{{ $app->name }}</option>
                    @endforeach
                @else
                    @foreach($servers as $server)
                        <option value="{{ $server->id }}" {{ $entity2 == $server->id ? 'selected' : '' }}>{{ $server->name }}</option>
                    @endforeach
                @endif
            </select>
            {{-- 4. NOUVEAU : Choisir la métrique --}}
            <select name="metric" class="filter-btn" onchange="this.form.submit()">
                @if($type == 'server')
                    <option value="cpu" {{ $metric == 'cpu' ? 'selected' : '' }}>CPU</option>
                    <option value="ram" {{ $metric == 'ram' ? 'selected' : '' }}>RAM</option>
                    <option value="disk" {{ $metric == 'disk' ? 'selected' : '' }}>Disque</option>
                    <option value="network" {{ $metric == 'network' ? 'selected' : '' }}>Réseau</option>
                @else
                    <option value="response_time" {{ $metric == 'response_time' ? 'selected' : '' }}>Temps de réponse</option>
                    <option value="error_rate" {{ $metric == 'error_rate' ? 'selected' : '' }}>Taux d'erreurs</option>
                    <option value="requests" {{ $metric == 'requests' ? 'selected' : '' }}>Trafic API</option>
                @endif
            </select>

            {{-- 5. Choisir la période --}}
            <select name="range" class="filter-btn" onchange="this.form.submit()">
                <option value="24h" {{ $range == '24h' ? 'selected' : '' }}>24 heures</option>
                <option value="7d" {{ $range == '7d' ? 'selected' : '' }}>7 jours</option>
                <option value="30d" {{ $range == '30d' ? 'selected' : '' }}>30 jours</option>
            </select>

            <button type="submit" class="usr-btn"><i class="fa-solid fa-code-compare"></i> Comparer</button>
        </div>
    </form>
</div>

@if($entity1 && $entity2)
    {{-- GRAPHIQUE DE COMPARAISON --}}
    <div class="panel" style="margin-top: 24px;">
        <div class="panel-header">
            <p>Comparaison : {{ $name1 }} vs {{ $name2 }}</p>
            <span class="badge badge-success" style="background: var(--dark-teal); color: white;">{{ $metricLabel }}</span>
        </div>
        <div class="alertChart" style="height: 400px; position: relative;">
            <canvas id="comparisonChart"></canvas>
        </div>
    </div>
@else
    <div class="panel" style="margin-top: 24px; text-align: center; padding: 40px; color: var(--text-muted);">
        <i class="fa-solid fa-code-compare" style="font-size: 32px; margin-bottom: 15px;"></i><br>
        Veuillez sélectionner deux éléments dans le menu ci-dessus pour lancer la comparaison.
    </div>
@endif

{{-- Passage des données au JavaScript --}}
<script>
    window.compareLabels = @json($chartLabels);
    window.compareData1 = @json($data1);
    window.compareData2 = @json($data2);
    window.compareName1 = @json($name1);
    window.compareName2 = @json($name2);
    window.compareMetric = @json($metricLabel);
</script>

@endsection