
<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-icon"><i class="fa-solid fa-heart-pulse"></i></div>
            <div class="logo-text">
                <p class="logo-title"><span class="pacifico">UniPulse</span></p>
                <p class="logo-subtitle">Monitor. Secure. Perform.</p>
            </div>
        </div>

        <nav class="sidebar-nav">

            <a href="#" class="nav-item active">
                <i class="fa-solid fa-gauge"></i><span>Dashboard</span>
            </a>

            {{-- Groupe repliable : Administration --}}
            <div class="nav-group">
                <button type="button" class="nav-group-toggle">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>Administration</span>
                    <i class="fa-solid fa-chevron-down nav-group-arrow"></i>
                </button>
                <div class="nav-group-items">
                    <a href="#" class="nav-item"><i class="fa-solid fa-users"></i><span>Utilisateurs</span></a>
                    <a href="#" class="nav-item"><i class="fa-solid fa-code"></i><span>API REST & Webhooks</span></a>
                    <a href="#" class="nav-item"><i class="fa-solid fa-window-restore"></i><span>Applications</span></a>
                    <a href="#" class="nav-item"><i class="fa-solid fa-server"></i><span>Serveurs</span></a>
                    <a href="#" class="nav-item"><i class="fa-solid fa-robot"></i><span>Agents Python</span></a>
                </div>
            </div>

            {{-- Groupe repliable : Monitoring --}}
            <div class="nav-group">
                <button type="button" class="nav-group-toggle">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Monitoring</span>
                    <i class="fa-solid fa-chevron-down nav-group-arrow"></i>
                </button>
                <div class="nav-group-items">
                    <a href="#" class="nav-item"><i class="fa-solid fa-window-restore"></i><span>Applications</span></a>
                    <a href="#" class="nav-item"><i class="fa-solid fa-server"></i><span>Serveurs</span></a>
                    <a href="#" class="nav-item"><i class="fa-solid fa-list"></i><span>Logs</span></a>
                </div>
            </div>

            {{-- Groupe repliable : Sécurité & Conformité --}}
            <div class="nav-group">
                <button type="button" class="nav-group-toggle">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Sécurité &amp; Conformité</span>
                    <i class="fa-solid fa-chevron-down nav-group-arrow"></i>
                </button>
                <div class="nav-group-items">
                    <a href="#" class="nav-item"><i class="fa-solid fa-bug"></i><span>Vulnérabilités</span></a>
                    <a href="#" class="nav-item"><i class="fa-solid fa-circle-check"></i><span>Conformité</span></a>
                    <a href="#" class="nav-item"><i class="fa-solid fa-file-lines"></i><span>Journaux d'audit</span></a>
                </div>
            </div>

            <div class="nav-divider"></div>

            <a href="#" class="nav-item">
                <i class="fa-solid fa-bell"></i><span>Alertes</span>
                <span class="nav-badge">6</span>
            </a>
            <a href="#" class="nav-item"><i class="fa-solid fa-chart-simple"></i><span>Reporting</span></a>

        </nav>

        <a href="#" class="sidebar-logout" id="logout-link">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Se déconnecter</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST">
            @csrf
        </form>
    </aside>
</div>