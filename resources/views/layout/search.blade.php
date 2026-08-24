@extends('layout.app')

@section('content')

<div class="page-title">

    <h1>Recherche</h1>

    <p>
        Résultats pour :
        <strong>{{ $query }}</strong>
    </p>

</div>


@if($query === '')

    <div class="empty-state">
        Entrez un terme dans la barre de recherche.
    </div>

@else

    {{-- APPLICATIONS --}}
    @if($applications->count())

        <div class="search-section">

            <h2>
                <i class="fa-solid fa-cubes"></i>
                Applications
            </h2>

            @foreach($applications as $application)

                <a
                    href="#"
                    class="search-result"
                >

                    <div>
                        <strong>
                            {{ $application->name }}
                        </strong>

                        <span>
                            {{ $application->applicationType?->name ?? 'Application' }}
                        </span>
                    </div>

                    <i class="fa-solid fa-chevron-right"></i>

                </a>

            @endforeach

        </div>

    @endif


    {{-- SERVEURS --}}
    @if($servers->count())

        <div class="search-section">

            <h2>
                <i class="fa-solid fa-server"></i>
                Serveurs
            </h2>

            @foreach($servers as $server)

                <a
                    href="{{ route('servers.show', $servers) }}"
                    class="search-result"
                >

                    <div>
                        <strong>
                            {{ $server->name }}
                        </strong>
                    </div>

                    <i class="fa-solid fa-chevron-right"></i>

                </a>

            @endforeach

        </div>

    @endif


    {{-- UTILISATEURS --}}
    @if($users->count())

        <div class="search-section">

            <h2>
                <i class="fa-solid fa-users"></i>
                Utilisateurs
            </h2>

            @foreach($users as $user)

                <a
                    href="{{ route('users.show', $user) }}"
                    class="search-result"
                >

                    <div>
                        <strong>
                            {{ $user->name }}
                        </strong>

                        <span>
                            {{ $user->email }}
                        </span>
                    </div>

                    <i class="fa-solid fa-chevron-right"></i>

                </a>

            @endforeach

        </div>

    @endif


    @if(
        !$applications->count() &&
        !$servers->count() &&
        !$users->count()
    )

        <div class="empty-state">

            Aucun résultat trouvé pour
            <strong>{{ $query }}</strong>.

        </div>

    @endif

@endif

@endsection