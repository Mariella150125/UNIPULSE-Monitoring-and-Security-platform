@extends('layout.app')

@section('content')
<div class="page-title">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1>Centre de Rapports</h1>
            <p>Historique, génération et exportation des rapports.</p>
        </div>
        <a href="{{ route('reports.create') }}" class="usr-btn-1">
            <i class="fa-solid fa-plus"></i> Créer un nouveau rapport
        </a>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <p>Historique des rapports générés </p>
        <a href="{{ route('reports.statistics') }}" class="btn btn-cancel"><i class="fa-solid fa-chart-line"></i> Voir Statistiques</a>
    </div>
    
    <table class="server-table">
        <thead>
            <tr>
                <th>Nom du fichier</th>
                <th>Type</th>
                <th>Format</th>
                <th>Responsable / Département</th>
                <th>Date</th>
                <th>Impression</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $report)
                <tr>
                    <td><strong>{{ $report->name }}</strong></td>
                    <td><span class="badge">{{ ucfirst($report->type) }}</span></td>
                    <td>
                        @if($report->format == 'pdf') <i class="fa-solid fa-file-pdf" style="color:var(--red);"></i> PDF
                        @elseif($report->format == 'excel') <i class="fa-solid fa-file-excel" style="color:var(--sage-green);"></i> Excel
                        @elseif($report->format == 'word') <i class="fa-solid fa-file-word" style="color:var(--dark-teal);"></i> Word
                        @elseif($report->format == 'image') <i class="fa-solid fa-image" style="color:var(--orange);"></i> Image
                        @endif
                    </td>
                    <td>
                        {{ $report->generator->name ?? 'N/A' }}<br>
                        <small style="color: var(--text-muted);">{{ $report->department ?? 'Non spécifié' }}</small>
                    </td>
                    <td>{{ $report->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($report->printed_copies > 0)
                            {{ $report->printed_copies }} ex. ({{ $report->printer_used }})
                        @else
                            <span style="color: var(--text-muted);">Non imprimé</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('reports.download', $report->id) }}" class="icon-btn" title="Télécharger" style="text-decoration: none;">
                            <i class="fa-solid fa-download"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding: 30px; color: var(--text-muted);">
                        Aucun rapport généré pour le moment.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection