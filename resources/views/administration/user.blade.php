@extends('layout.app')
@section('Utilisateurs')
@section('content')

            <div class="page-title">
                <h1>Gestion des Utilisateurs</h1>
            </div>
            <button class= "usr-btn">
                <i class="fa-solid fa-user-plus"></i>
                 Add User
            </button>
            {{-- Rangée de 12 KPI, une seule ligne, défilement horizontal --}}
            <div class="usr-kpi-row">
                <div class="kpi-card">
                    <div class="kpi-icon c-teal"><i class="fa-solid fa-user"></i></div>
                    <p class="kpi-label">Nombre d'utilisateurs</p><p class="kpi-value">13</p>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon c-sage"><i class="fa-solid fa-user-check"></i></div>
                    <p class="kpi-label">Utilisateurs Actifs</p><p class="kpi-value">15</p>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon c-teal"><i class="fa-solid fa-user-shield"></i></div>
                    <p class="kpi-label">Administrateurs</p><p class="kpi-value">35</p>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon c-sage"><i class="fa-solid fa-user-xmark"></i></div>
                    <p class="kpi-label">Utilisateurs Inactifs</p><p class="kpi-value">30</p>
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
                        <tr>
                            <td>Mariella Ngwambe</td>
                            <td>mariella@entreprise.com</td>
                            <td>Développeuse</td>
                            <td>Software Dev</td>
                            <td><span class="status-dot online"></span>Actif</td>
                            <td>Il y a 2 min</td>
                            <td class="grid-6">
                                <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                                <button class="icon-btn"><i class="fa-solid fa-trash"></i></button>
                                <button class="icon-btn"><i class="fa-solid fa-ellipsis"></i></button>
                            </td>
                            </td>
                        </tr>
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