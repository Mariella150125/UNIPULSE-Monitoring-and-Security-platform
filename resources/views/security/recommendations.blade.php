@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Centre de Recommandations</h1>
    <p>Recommandations prioritaires et liens de remédiation (Guidelines OWASP).</p>
</div>

@if(session('success'))
    <div class="success-message"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif

<div class="panel">
    <div class="panel-header">
        <p>Recommandations Dynamiques</p>
    </div>
    <table class="server-table">
        <thead>
            <tr>
                <th>Gravité</th>
                <th>Ressource</th>
                <th>Contrôle Échoué</th>
                <th>Recommandation</th>
                <th>Guideline</th>
            </tr>
        </thead>
        <tbody>
            @forelse($allRecommendations as $rec)
            <tr>
                <td>
                    @if($rec['severity'] == 'HIGH')
                        <span class="badge badge-critical">HIGH</span>
                    @else
                        <span class="badge badge-major">MEDIUM</span>
                    @endif
                </td>
                <td><strong>{{ $rec['app_name'] }}</strong></td>
                <td>{{ $rec['check_name'] }}</td>
                <td>{{ $rec['remediation'] }}</td>
                <td><a href="{{ $rec['guideline'] }}" target="_blank" class="usr-btn-1">Voir Guideline</a></td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; padding: 20px; color: var(--sage-green);">
                    <i class="fa-solid fa-circle-check" style="font-size: 24px; margin-bottom: 10px;"></i><br>
                    Toutes les applications sont conformes !
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection