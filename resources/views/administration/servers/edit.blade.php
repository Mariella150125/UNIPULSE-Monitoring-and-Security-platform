@extends('layout.app')

@section('content')

<div class="page-title">
    <h1>Modifier l'utilisateur</h1>
    <p>{{ $user->name }}</p>
</div>

<form
    action="{{ route('users.update', $user->id) }}"
    method="POST"
>

    @csrf
    @method('PUT')

    <div class="form-grid">

        <div class="form-group">
            <label for="name">Nom</label>

            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $user->name) }}"
                required
            >

            @error('name')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>


        <div class="form-group">
            <label for="email">Email</label>

            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $user->email) }}"
                required
            >

            @error('email')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>


        <div class="form-group">
            <label for="telephone">Téléphone</label>

            <input
                type="text"
                id="telephone"
                name="telephone"
                value="{{ old('telephone', $user->telephone) }}"
                required
            >

            @error('telephone')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>


        <div class="form-group">
            <label for="role">Fonction</label>

            <input
                type="text"
                id="role"
                name="role"
                value="{{ old('role', $user->role) }}"
                required
            >

            @error('role')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>


        <div class="form-group">
            <label for="department">Département</label>

            <input
                type="text"
                id="department"
                name="department"
                value="{{ old('department', $user->department) }}"
                required
            >

            @error('department')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

    </div>


    <div class="form-actions">

        <a href="{{ route('users') }}" class="btn-cancel">
            Annuler
        </a>

        <button type="submit" class="btn-save">
            Enregistrer les modifications
        </button>

    </div>

</form>

@endsection