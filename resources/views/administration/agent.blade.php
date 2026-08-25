@extends('layout.app')

@section('agent')

@section('content')

    {{-- =========================
         TITRE
    ========================= --}}

    <div class="page-title">
        <h1>Connecteurs</h1>
        <p>
            Gérez et surveillez les connexions avec vos plateformes
            de monitoring et de sécurité.
        </p>
    </div>


    {{-- =========================
         BOUTON AJOUTER
    ========================= --}}

    <button
        type="button"
        class="usr-btn"
        data-modal-open="connector-modal"
    >
        <i class="fa-solid fa-plus"></i>
        Ajouter un connecteur
    </button>


    {{-- =========================
         KPIs
    ========================= --}}

    <div class="usr-kpi-row">

        {{-- TOTAL --}}
        <div class="kpi-card">

            <div class="kpi-icon c-teal">
                <i class="fa-solid fa-plug"></i>
            </div>

            <p class="kpi-label">
                Connecteurs enregistrés
            </p>

            <p class="kpi-value">
                4
            </p>

        </div>


        {{-- CONNECTES --}}
        <div class="kpi-card">

            <div class="kpi-icon c-sage">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <p class="kpi-label">
                Connecteurs connectés
            </p>

            <p class="kpi-value">
                3
            </p>

        </div>


        {{-- ERREURS --}}
        <div class="kpi-card">

            <div class="kpi-icon c-red">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>

            <p class="kpi-label">
                Connecteurs en erreur
            </p>

            <p class="kpi-value">
                1
            </p>

        </div>


        {{-- JAMAIS TESTES --}}
        <div class="kpi-card">

            <div class="kpi-icon c-orange">
                <i class="fa-solid fa-clock"></i>
            </div>

            <p class="kpi-label">
                Jamais testés
            </p>

            <p class="kpi-value">
                0
            </p>

        </div>

    </div>


    {{-- =========================
         CONTENU PRINCIPAL
    ========================= --}}

    <div class="agent-layout">


        {{-- =========================
             TABLEAU DES CONNECTEURS
        ========================= --}}

        <div class="panel">

            {{-- HEADER --}}
            <div class="panel-header">

                <div class="search-bar">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input
                        type="text"
                        placeholder="Rechercher un connecteur..."
                    >

                </div>


                <div class="search-filter">

                    <select>

                        <option>
                            Tous les types
                        </option>

                        <option>
                            Prometheus
                        </option>

                        <option>
                            Wazuh
                        </option>

                    </select>


                    <select>

                        <option>
                            Tous les statuts
                        </option>

                        <option>
                            Connecté
                        </option>

                        <option>
                            Erreur
                        </option>

                        <option>
                            Jamais testé
                        </option>

                    </select>

                </div>

            </div>


            {{-- TABLEAU --}}

            <table class="server-table">

                <thead>

                    <tr>

                        <th>Type</th>

                        <th>Nom</th>

                        <th>URL</th>

                        <th>Statut</th>

                        <th>Dernière vérification</th>

                        <th>Configuré par</th>

                        <th>Actions</th>

                    </tr>

                </thead>


                <tbody>


                    {{-- =========================
                         PROMETHEUS
                    ========================= --}}

                    <tr class="agent-selected">

                        <td>

                            <span class="connector-type">

                                <i class="fa-solid fa-chart-line"></i>

                                Prometheus

                            </span>

                        </td>


                        <td>
                            Prometheus — Production
                        </td>


                        <td>
                            http://192.168.1.50:9090
                        </td>


                        <td>

                            <span class="status-dot online"></span>

                            Connecté

                        </td>


                        <td>
                            Il y a 2 min
                        </td>


                        <td>
                            Admin
                        </td>


                        <td>

                            {{-- VOIR --}}
                            <button
                                type="button"
                                class="icon-btn"
                                title="Voir"
                            >
                                <i class="fa-solid fa-eye"></i>
                            </button>


                            {{-- MODIFIER --}}
                            <button
                                type="button"
                                class="icon-btn"
                                title="Modifier"
                            >
                                <i class="fa-solid fa-pen"></i>
                            </button>


                            {{-- TESTER --}}
                            <button
                                type="button"
                                class="icon-btn"
                                title="Tester la connexion"
                            >
                                <i class="fa-solid fa-plug"></i>
                            </button>


                            {{-- SUPPRIMER --}}
                            <button
                                type="button"
                                class="icon-btn"
                                title="Supprimer"
                            >
                                <i class="fa-solid fa-trash"></i>
                            </button>

                        </td>

                    </tr>


                    {{-- =========================
                         WAZUH
                    ========================= --}}

                    <tr>

                        <td>

                            <span class="connector-type">

                                <i class="fa-solid fa-shield-halved"></i>

                                Wazuh

                            </span>

                        </td>


                        <td>
                            Wazuh — Production
                        </td>


                        <td>
                            https://192.168.1.60:55000
                        </td>


                        <td>

                            <span class="status-dot online"></span>

                            Connecté

                        </td>


                        <td>
                            Il y a 5 min
                        </td>


                        <td>
                            Admin
                        </td>


                        <td>

                            <button
                                type="button"
                                class="icon-btn"
                                title="Voir"
                            >
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            <button
                                type="button"
                                class="icon-btn"
                                title="Modifier"
                            >
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <button
                                type="button"
                                class="icon-btn"
                                title="Tester la connexion"
                            >
                                <i class="fa-solid fa-plug"></i>
                            </button>

                            <button
                                type="button"
                                class="icon-btn"
                                title="Supprimer"
                            >
                                <i class="fa-solid fa-trash"></i>
                            </button>

                        </td>

                    </tr>


                    {{-- =========================
                         PROMETHEUS ERREUR
                    ========================= --}}

                    <tr>

                        <td>

                            <span class="connector-type">

                                <i class="fa-solid fa-chart-line"></i>

                                Prometheus

                            </span>

                        </td>


                        <td>
                            Prometheus — Staging
                        </td>


                        <td>
                            http://192.168.1.70:9090
                        </td>


                        <td>

                            <span class="status-dot offline"></span>

                            Erreur

                        </td>


                        <td>
                            Il y a 15 min
                        </td>


                        <td>
                            Admin
                        </td>


                        <td>

                            <button
                                type="button"
                                class="icon-btn"
                                title="Voir"
                            >
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            <button
                                type="button"
                                class="icon-btn"
                                title="Modifier"
                            >
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <button
                                type="button"
                                class="icon-btn"
                                title="Tester la connexion"
                            >
                                <i class="fa-solid fa-plug"></i>
                            </button>

                            <button
                                type="button"
                                class="icon-btn"
                                title="Supprimer"
                            >
                                <i class="fa-solid fa-trash"></i>
                            </button>

                        </td>

                    </tr>


                    {{-- =========================
                         WAZUH JAMAIS TESTE
                    ========================= --}}

                    <tr>

                        <td>

                            <span class="connector-type">

                                <i class="fa-solid fa-shield-halved"></i>

                                Wazuh

                            </span>

                        </td>


                        <td>
                            Wazuh — Test
                        </td>


                        <td>
                            https://192.168.1.80:55000
                        </td>


                        <td>

                            <span
                                class="status-dot"
                                style="background:var(--text-muted);"
                            ></span>

                            Jamais testé

                        </td>


                        <td>
                            Jamais
                        </td>


                        <td>
                            Admin
                        </td>


                        <td>

                            <button
                                type="button"
                                class="icon-btn"
                                title="Voir"
                            >
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            <button
                                type="button"
                                class="icon-btn"
                                title="Modifier"
                            >
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <button
                                type="button"
                                class="icon-btn"
                                title="Tester la connexion"
                            >
                                <i class="fa-solid fa-plug"></i>
                            </button>

                            <button
                                type="button"
                                class="icon-btn"
                                title="Supprimer"
                            >
                                <i class="fa-solid fa-trash"></i>
                            </button>

                        </td>

                    </tr>


                </tbody>

            </table>


            {{-- PAGINATION --}}

            <div class="pagination">

                <button
                    class="pagination-btn"
                    disabled
                >
                    <i class="fa-solid fa-chevron-left"></i>
                </button>

                <a
                    href="#"
                    class="pagination-btn active-page"
                >
                    1
                </a>

                <button
                    class="pagination-btn"
                    disabled
                >
                    <i class="fa-solid fa-chevron-right"></i>
                </button>

            </div>

        </div>



        {{-- =========================
             PANNEAU DETAIL
        ========================= --}}

        <div class="panel agent-detail-panel">


            {{-- HEADER CONNECTEUR --}}

            <div class="agent-detail-header">

                <div
                    class="kpi-icon c-teal"
                    style="width:40px;height:40px;font-size:18px;"
                >

                    <i class="fa-solid fa-chart-line"></i>

                </div>


                <div>

                    <p
                        style="
                            margin:0;
                            font-weight:600;
                            font-size:16px;
                        "
                    >
                        Prometheus — Production
                    </p>

                    <p
                        style="
                            margin:2px 0 0;
                            font-size:12px;
                            color:var(--text-muted);
                        "
                    >
                        http://192.168.1.50:9090
                    </p>

                </div>


                <span
                    class="status-dot online"
                    style="
                        width:10px;
                        height:10px;
                        margin-left:auto;
                    "
                ></span>

            </div>


            {{-- INFORMATIONS CONNECTEUR --}}

            <div class="agent-detail-stats">


                <div class="agent-detail-stat">

                    <p class="agent-detail-stat-label">
                        Type
                    </p>

                    <p class="agent-detail-stat-value">
                        Prometheus
                    </p>

                </div>


                <div class="agent-detail-stat">

                    <p class="agent-detail-stat-label">
                        Statut
                    </p>

                    <p
                        class="agent-detail-stat-value"
                        style="color:var(--sage-green);"
                    >
                        Connecté
                    </p>

                </div>


                <div class="agent-detail-stat">

                    <p class="agent-detail-stat-label">
                        Dernière vérification
                    </p>

                    <p class="agent-detail-stat-value">
                        Il y a 2 min
                    </p>

                </div>


                <div class="agent-detail-stat">

                    <p class="agent-detail-stat-label">
                        Configuré par
                    </p>

                    <p class="agent-detail-stat-value">
                        Admin
                    </p>

                </div>


            </div>


            {{-- TESTER MAINTENANT --}}

            <div
                style="
                    margin-top:15px;
                    margin-bottom:15px;
                "
            >

                <button
                    type="button"
                    class="usr-btn"
                >

                    <i class="fa-solid fa-plug"></i>

                    Tester maintenant

                </button>

            </div>


            {{-- HISTORIQUE --}}

            <div
                class="panel-header"
                style="margin-top:10px;"
            >

                <p>
                    Historique des connexions
                </p>

                <span
                    style="
                        font-size:12px;
                        color:var(--text-muted);
                    "
                >
                    20 derniers tests
                </span>

            </div>


            {{-- LISTE DES LOGS --}}

            <div class="connector-history">


                <div class="history-item">

                    <span class="status-dot online"></span>

                    <div>

                        <strong>
                            Connexion réussie
                        </strong>

                        <p>
                            Il y a 2 min
                        </p>

                    </div>

                </div>


                <div class="history-item">

                    <span class="status-dot online"></span>

                    <div>

                        <strong>
                            Connexion réussie
                        </strong>

                        <p>
                            Il y a 7 min
                        </p>

                    </div>

                </div>


                <div class="history-item">

                    <span class="status-dot offline"></span>

                    <div>

                        <strong>
                            Échec de connexion
                        </strong>

                        <p>
                            Il y a 12 min
                        </p>

                    </div>

                </div>


                <div class="history-item">

                    <span class="status-dot online"></span>

                    <div>

                        <strong>
                            Connexion réussie
                        </strong>

                        <p>
                            Il y a 17 min
                        </p>

                    </div>

                </div>


                <div class="history-item">

                    <span class="status-dot online"></span>

                    <div>

                        <strong>
                            Connexion réussie
                        </strong>

                        <p>
                            Il y a 22 min
                        </p>

                    </div>

                </div>


            </div>

        </div>

    </div>


    {{-- =========================
         MODALE AJOUTER CONNECTEUR
    ========================= --}}

    @include('administration.connectors.connector-modal')


    {{-- =========================
         SYNCHRONISATION
    ========================= --}}

    <p class="sync-time">
        Dernière synchronisation : il y a 2 min
    </p>


@endsection