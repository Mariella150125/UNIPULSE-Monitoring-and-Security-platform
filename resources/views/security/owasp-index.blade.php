@extends('layout.app')

@section('content')

<div class="page-title">
    <a href="{{ route('security.compliance') }}" class="btn btn-cancel" style="margin-bottom: 15px;">
        <i class="fa-solid fa-arrow-left"></i> Retour à la Sécurité
    </a>
    <h1>Gestion OWASP Top 10</h1>
    <p>Ajoutez, modifiez ou désactivez les catégories de conformité.</p>
</div>

@if(session('success'))
    <div class="success-message"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif

<div class="panel">
    <!-- FORMULAIRE D'AJOUT -->
    <form action="{{ route('owasp-categories.store') }}" method="POST" style="display: grid; grid-template-columns: 80px 2fr 3fr 3fr auto; gap: 15px; margin-bottom: 20px; align-items: end;">
        @csrf
        <div class="input-group" style="margin: 0;">
            <label style="font-size: 12px;">Code</label>
            <input type="text" name="code" required placeholder="A02">
        </div>
        <div class="input-group" style="margin: 0;">
            <label style="font-size: 12px;">Nom</label>
            <input type="text" name="name" required>
        </div>
        <div class="input-group" style="margin: 0;">
            <label style="font-size: 12px;">Description</label>
            <input type="text" name="description">
        </div>
        <div class="input-group" style="margin: 0;">
            <label style="font-size: 12px;">URL OWASP</label>
            <input type="url" name="url" required placeholder="https://owasp.org/...">
        </div>
        <button type="submit" class="usr-btn"><i class="fa-solid fa-plus"></i> Ajouter</button>
    </form>

    <!-- TABLEAU DES CATÉGORIES -->
    <table class="server-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Nom</th>
                <th>URL OWASP</th>
                <th>Affiché ?</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $cat)
            <tr>
                <form action="{{ route('owasp-categories.update', $cat->id) }}" method="POST">
                    @csrf @method('PUT')
                    <td><input type="text" name="code" value="{{ $cat->code }}" style="border: none; background: transparent; font-weight: bold; width: 50px;"></td>
                    <td><input type="text" name="name" value="{{ $cat->name }}" style="border: none; background: transparent; width: 100%;"></td>
                    <td><input type="url" name="url" value="{{ $cat->url }}" style="border: none; background: transparent; width: 100%; font-size: 12px;"></td>
                    <td style="text-align: center;">
                        <a href="{{ route('owasp-categories.toggle', $cat->id) }}" class="status-dot {{ $cat->is_active ? 'online' : 'offline' }}" title="Activer/Désactiver"></a>
                    </td>
                    <td>
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" class="icon-btn" title="Enregistrer" style="border: none; background: transparent; cursor: pointer; color: var(--sage-green);">
                                <i class="fa-solid fa-check"></i>
                            </button>
                        </div>
                    </td>
                </form>
                <td style="border: none;">
                    <form action="{{ route('owasp-categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="icon-btn" title="Supprimer" style="border: none; background: transparent; cursor: pointer; color: var(--red);">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection