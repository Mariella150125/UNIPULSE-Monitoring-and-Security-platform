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

            <a href="/content" class="nav-item {{ request()->is('content') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge"></i><span>Dashboard</span>
            </a>

            {{-- Groupe repliable : Administration --}}
            <div class="nav-group {{ (request()->is('user') || request()->is('web') || request()->is('appli') || request()->is('server') || request()->is('agent')) ? 'open' : '' }}">
                <button type="button" class="nav-group-toggle">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>Administration</span>
                    <i class="fa-solid fa-chevron-down nav-group-arrow"></i>
                </button>
                <div class="nav-group-items" style="{{ (request()->is('users') || request()->is('web') || request()->is('appli') || request()->is('server') || request()->is('agent')) ? 'max-height: 500px;' : '' }}">
                    <a href="/users" class="nav-item {{ request()->is('users') ? 'active' : '' }}"><i class="fa-solid fa-users"></i><span>Utilisateurs</span></a>
                    <a href="/web" class="nav-item {{ request()->is('web') ? 'active' : '' }}"><i class="fa-solid fa-code"></i><span>API REST & Webhooks</span></a>
                    <a href="/appli" class="nav-item {{ request()->is('appli') ? 'active' : '' }}"><i class="fa-solid fa-window-restore"></i><span>Applications</span></a>
                    <a href="/server" class="nav-item {{ request()->is('server') ? 'active' : '' }}"><i class="fa-solid fa-server"></i><span>Serveurs</span></a>
                    <a href="/agent" class="nav-item {{ request()->is('agent') ? 'active' : '' }}"><i class="fa-solid fa-robot"></i><span>Agents Python</span></a>
                </div>
            </div>

            {{-- Groupe repliable : Monitoring --}}
            <div class="nav-group {{ (request()->is('monitoring/*') || request()->is('logs')) ? 'open' : '' }}">
                <button type="button" class="nav-group-toggle">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Monitoring</span>
                    <i class="fa-solid fa-chevron-down nav-group-arrow"></i>
                </button>
                <div class="nav-group-items" style="{{ (request()->is('monitoring/*') || request()->is('logs')) ? 'max-height: 500px;' : '' }}">
                    <a href="/monitoring/apps" class="nav-item {{ request()->is('monitoring/apps') ? 'active' : '' }}"><i class="fa-solid fa-window-restore"></i><span>Applications</span></a>
                    <a href="/monitoring/servers" class="nav-item {{ request()->is('monitoring/servers') ? 'active' : '' }}"><i class="fa-solid fa-server"></i><span>Serveurs</span></a>
                    <a href="/logs" class="nav-item {{ request()->is('logs') ? 'active' : '' }}"><i class="fa-solid fa-list"></i><span>Logs</span></a>
                </div>
            </div>

            {{-- Groupe repliable : Sécurité & Conformité --}}
            <div class="nav-group {{ request()->is('security/*') ? 'open' : '' }}">
                <button type="button" class="nav-group-toggle">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Sécurité &amp; Conformité</span>
                    <i class="fa-solid fa-chevron-down nav-group-arrow"></i>
                </button>
                <div class="nav-group-items" style="{{ request()->is('security/*') ? 'max-height: 500px;' : '' }}">
                    <a href="/security/vulnerabilities" class="nav-item {{ request()->is('security/vulnerabilities') ? 'active' : '' }}"><i class="fa-solid fa-bug"></i><span>Vulnérabilités</span></a>
                    <a href="/security/compliance" class="nav-item {{ request()->is('security/compliance') ? 'active' : '' }}"><i class="fa-solid fa-circle-check"></i><span>Conformité</span></a>
                    <a href="/security/audit-logs" class="nav-item {{ request()->is('security/audit-logs') ? 'active' : '' }}"><i class="fa-solid fa-file-lines"></i><span>Journaux d'audit</span></a>
                </div>
            </div>

            <div class="nav-divider"></div>

            <a href="/alerts" class="nav-item {{ request()->is('alerts') ? 'active' : '' }}">
                <i class="fa-solid fa-bell"></i><span>Alertes</span>
                <span class="nav-badge">6</span>
            </a>
            <a href="/reporting" class="nav-item {{ request()->is('reporting') ? 'active' : '' }}"><i class="fa-solid fa-chart-simple"></i><span>Reporting</span></a>

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