@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Modifier l'utilisateur</h1>
    <p>{{ $user->name }}</p>
</div>

<form
    action="{{ route('users.update', $user->id) }}"
    method="POST"
    class="entity-details"
>

    @csrf
    @method('PUT')

    <div class="entity-details-body">

        <div class="modal-grid-2">

            <div class="input-group">
                <label for="name" class="detail-label">Nom</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    required
                >
                @error('name')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="input-group">
                <label for="email" class="detail-label">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    required
                >
                @error('email')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="input-group">
                <label for="telephone" class="detail-label">Téléphone</label>
                <input
                    type="text"
                    id="telephone"
                    name="telephone"
                    value="{{ old('telephone', $user->telephone) }}"
                >
                @error('telephone')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="input-group">
                <label for="role" class="detail-label">Fonction</label>
                <input
                    type="text"
                    id="role"
                    name="role"
                    value="{{ old('role', $user->role) }}"
                >
                @error('role')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="input-group">
                <label for="department" class="detail-label">Département</label>
                <input
                    type="text"
                    id="department"
                    name="department"
                    value="{{ old('department', $user->department) }}"
                >
                @error('department')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

        </div>

    </div>

    <div class="form-actions">
        <a href="/users" class="btn btn-cancel">Annuler</a>
        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
    </div>

</form>

@endsection