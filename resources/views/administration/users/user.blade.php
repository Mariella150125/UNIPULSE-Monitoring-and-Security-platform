@extends('layout.app')
@section('Utilisateurs')
@section('content')

            <div class="page-title">
                <h1>Gestion des Utilisateurs</h1>
            </div>
            <a href="{{  route('sign') }}" class="usr-btn">
                <i class="fa-solid fa-user-plus"></i>
                 Add User
            </a>
            {{-- Rangée de 12 KPI, une seule ligne, défilement horizontal --}}
            <div class="usr-kpi-row">
                <div class="kpi-card">
                    <div class="kpi-icon c-teal"><i class="fa-solid fa-user"></i></div>
                    <p class="kpi-label">Nombre d'utilisateurs</p><p class="kpi-value">{{ $totalUsers }}</p>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon c-sage"><i class="fa-solid fa-user-check"></i></div>
                    <p class="kpi-label">Utilisateurs Actifs</p><p class="kpi-value">{{ $activeUsers }}</p>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon c-teal"><i class="fa-solid fa-user-shield"></i></div>
                    <p class="kpi-label">Administrateurs</p><p class="kpi-value">{{ $admins}}</p>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon c-sage"><i class="fa-solid fa-user-xmark"></i></div>
                    <p class="kpi-label">Utilisateurs Inactifs</p><p class="kpi-value">{{ $inactiveUsers }}</p>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <div class="search-bar">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Rechercher un utilisateur...">
                    </div>
                    <div class="grid-3">
                        <select class="filter-btn">
                            <option>Tous les rôles</option>
                            <option>Admin</option>
                            <option>UI/UX Designer</option>
                            <option>Développeur</option>
                        </select>
                        <select class="filter-btn">
                            <option>Tous les statuts</option>
                            <option>Actif</option>
                            <option>Inactif</option>
                        </select>
                        <select class="filter-btn">
                            <option>Software Dev</option>
                            <option>Application Support</option>
                        </select>
                    </div>
                </div>

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
                        @foreach ($users as $user)
                            
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role }}</td>
                                <td>{{ $user->department }}</td>
                                <td><span class="status-dot online"></span>Actif</td>
                                <td>--</td>
                                <td class="grid-6">
                                    <a href="{{ route('users.show' , $user->id) }}" class="icon-btn">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href= "{{ route('users.edit' , $user->id) }}" class="icon-btn">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="icon-btn">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                    
                                </td>
                            </tr>
                         @endforeach
                    </tbody>
                </table>

                <div class="pagination">
                    <button class="pagination-btn"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="pagination-btn active-page">1</button>
                    <button class="pagination-btn">2</button>
                    <button class="pagination-btn"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>
@endsection