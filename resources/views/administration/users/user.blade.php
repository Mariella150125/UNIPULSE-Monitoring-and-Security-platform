@extends('layout.app')

@section('content')

@if ($errors->any())
        <div class="flash-message error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
@if(session('success'))
    <div class="success-message" id="success-message">
        <i class="fa-solid fa-circle-check"></i>
        {{ session('success') }}
    </div>
@endif
<div class="page-title">
    <h1>Gestion des Utilisateurs</h1>
</div>
<a href="{{ route('sign') }}" class="usr-btn">
    <i class="fa-solid fa-user-plus"></i>
    Add User
</a>


{{-- =========================
     KPI
========================= --}}

<div class="usr-kpi-row">

    <div class="kpi-card">
        <div class="kpi-icon c-teal">
            <i class="fa-solid fa-user"></i>
        </div>

        <p class="kpi-label">Nombre d'utilisateurs</p>
        <p class="kpi-value">{{ $totalUsers }}</p>
    </div>


    <div class="kpi-card">
        <div class="kpi-icon c-sage">
            <i class="fa-solid fa-user-check"></i>
        </div>

        <p class="kpi-label">Utilisateurs Actifs</p>
        <p class="kpi-value">{{ $activeUsers }}</p>
    </div>


    <div class="kpi-card">
        <div class="kpi-icon c-teal">
            <i class="fa-solid fa-user-shield"></i>
        </div>

        <p class="kpi-label">Administrateurs</p>
        <p class="kpi-value">{{ $admins }}</p>
    </div>


    <div class="kpi-card">
        <div class="kpi-icon c-sage">
            <i class="fa-solid fa-user-xmark"></i>
        </div>

        <p class="kpi-label">Utilisateurs Inactifs</p>
        <p class="kpi-value">{{ $inactiveUsers }}</p>
    </div>

</div>



{{-- =========================
     RECHERCHE + FILTRES
========================= --}}

<div class="panel">

    <form method="GET" action="{{ route('users') }}">

        <div class="panel-header">


            {{-- RECHERCHE --}}

            <div class="search-bar">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    name="search"
                    placeholder="Rechercher un utilisateur..."
                    value="{{ request('search') }}"
                >

            </div>



            <div class="grid-3">


                {{-- ROLE --}}

                <select name="role" class="filter-btn">

                    <option value="">
                        Tous les rôles
                    </option>

                    <option value="Admin"
                        @selected(request('role') == 'Admin')>
                        Admin
                    </option>

                    <option value="DevOps"
                        @selected(request('role') == 'DevOps')>
                        DevOps
                    </option>

                    <option value="Développeur"
                        @selected(request('role') == 'Développeur')>
                        Développeur
                    </option>

                </select>



                {{-- STATUT --}}

                <select name="status" class="filter-btn">

                    <option value="">
                        Tous les statuts
                    </option>

                    <option value="actif"
                        @selected(request('status') == 'actif')>
                        Actif
                    </option>

                    <option value="inactif"
                        @selected(request('status') == 'inactif')>
                        Inactif
                    </option>

                </select>



                {{-- DEPARTEMENT --}}

                <select name="department" class="filter-btn">

                    <option value="">
                        Tous les départements
                    </option>

                    <option value="Technologie"
                        @selected(request('department') == 'Technologie')>
                        Technologie
                    </option>

                    <option value="QAT"
                        @selected(request('department') == 'QAT')>
                        QAT
                    </option>

                    <option value="Application Support"
                        @selected(request('department') == 'Application Support')>
                        Application Support
                    </option>

                    <option value="RSSI"
                        @selected(request('department') == 'RSSI')>
                        RSSI
                    </option>

                </select>

            </div>



            {{-- BOUTON FILTRER --}}

            <button type="submit" class="filter-btn">
                <i class="fa-solid fa-filter"></i>
                Filtrer
            </button>

        </div>

    </form>



    {{-- =========================
         TABLEAU
    ========================= --}}

    <table class="server-table">

        <thead>

            <tr>
                <th>Noms</th>
                <th>Email</th>
                <th>Fonction</th>
                <th>Département</th>
                <th>Statut</th>
                <th>Last Login</th>
                <th>Actions</th>
            </tr>

        </thead>


        <tbody>

            @forelse ($users as $user)

                <tr>

                    <td>
                        {{ $user->name }}
                    </td>

                    <td>
                        {{ $user->email }}
                    </td>

                    <td>
                        {{ $user->role }}
                    </td>

                    <td>
                        {{ $user->department }}
                    </td>


                    {{-- STATUT --}}

                    <td>

                        @if($user->status === 'actif')

                            <span class="status-dot online"></span>
                            Actif

                        @else

                            <span class="status-dot offline"></span>
                            Inactif

                        @endif

                    </td>


                    {{-- LAST LOGIN --}}

                    <td>
                        {{ $user->last_login ? $user->last_login->format('d/m/Y H:i') : 'Jamais' }}
                    </td>


                    {{-- ACTIONS --}}

                    <td class="grid-6">

                        {{-- SHOW --}}

                        <a
                            href="{{ route('users.show', $user->id) }}"
                            class="icon-btn"
                            title="Voir"
                        >
                            <i class="fa-solid fa-eye"></i>
                        </a>


                        {{-- EDIT --}}

                        <a
                            href="{{ route('users.edit', $user->id) }}"
                            class="icon-btn"
                            title="Modifier"
                        >
                            <i class="fa-solid fa-pen"></i>
                        </a>


                        {{-- DELETE --}}

                        <form
                            action="{{ route('users.destroy', $user->id) }}"
                            method="POST"
                        >

                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="icon-btn"
                                title="Supprimer"
                                onclick="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?')"
                            >
                                <i class="fa-solid fa-trash"></i>
                            </button>

                        </form>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="7" style="text-align: center;">
                        Aucun utilisateur trouvé.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>



    {{-- =========================
         PAGINATION
    ========================= --}}

    <div class="pagination">


        {{-- PRECEDENTE --}}

        @if ($users->onFirstPage())

            <button
                class="pagination-btn"
                disabled
            >
                <i class="fa-solid fa-chevron-left"></i>
            </button>

        @else

            <a
                href="{{ $users->previousPageUrl() }}"
                class="pagination-btn"
            >
                <i class="fa-solid fa-chevron-left"></i>
            </a>

        @endif



        {{-- NUMEROS --}}

        @for ($page = 1; $page <= $users->lastPage(); $page++)

            @if ($page == $users->currentPage())

                <a
                    href="{{ $users->url($page) }}"
                    class="pagination-btn active-page"
                >
                    {{ $page }}
                </a>

            @else

                <a
                    href="{{ $users->url($page) }}"
                    class="pagination-btn"
                >
                    {{ $page }}
                </a>

            @endif

        @endfor



        {{-- SUIVANTE --}}

        @if ($users->hasMorePages())

            <a
                href="{{ $users->nextPageUrl() }}"
                class="pagination-btn"
            >
                <i class="fa-solid fa-chevron-right"></i>
            </a>

        @else

            <button
                class="pagination-btn"
                disabled
            >
                <i class="fa-solid fa-chevron-right"></i>
            </button>

        @endif

    </div>

</div>
@endsection