<div class="main-area">
    

    <header class="topbar">
        <button class="sidebar-toggle" id="sidebar-toggle">
            <i class="fa-solid fa-bars"></i>
        </button>
        <form
            action="{{ route('global.search') }}"
            method="GET"
            class="topbar-search"
        >
            <i class="fa-solid fa-magnifying-glass"></i>
            <input 
                type="text" 
                name="q"
                value="{{ request('q') }}"
                placeholder="Rechercher..."
                autocomplete="off"
            >
            <button type="submit" class="search-submit">
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <div class="date-range-wrapper">

            <button
                type="button"
                class="icon-btn"
                id="date-range-toggle"
            >
                <span id="date-range-label">
                    @php
                        $range = request('range', '7');
                        $label = match($range) {
                            'today' => __("Aujourd'hui"),
                            '7'     => now()->subDays(6)->translatedFormat('d M.') . ' - ' . now()->translatedFormat('d M. Y'),
                            '30'    => now()->subDays(29)->translatedFormat('d M.') . ' - ' . now()->translatedFormat('d M. Y'),
                            '90'    => now()->subDays(89)->translatedFormat('d M.') . ' - ' . now()->translatedFormat('d M. Y'),
                            default => now()->subDays(6)->translatedFormat('d M.') . ' - ' . now()->translatedFormat('d M. Y'),
                        };
                    @endphp
                    {{ $label }}
                </span>
                <i class="fa-solid fa-chevron-down"></i>
            </button>

            <div class="date-range-menu" id="date-range-menu">
                <button type="button" data-range="today">Aujourd'hui</button>
                <button type="button" data-range="7">7 derniers jours</button>
                <button type="button" data-range="30">30 derniers jours</button>
                <button type="button" data-range="90">90 derniers jours</button>
                <button type="button" data-range="custom">
                    <i class="fa-regular fa-calendar"></i>
                    Période personnalisée
                </button>
            </div>

        </div>
        <div class="notif-wrapper" style="position: relative;">
            <button class="icon-btn notif-btn" id="notif-toggle" style="position: relative;">
                <i class="fa-regular fa-bell"></i>
                <span class="notif-badge" id="notif-count" style="display: none;">0</span>
            </button>

            <!-- Dropdown des notifications -->
            <div class="notif-dropdown" id="notif-dropdown" style="display: none; position: absolute; top: 100%; right: 0; width: 350px; background: var(--panel-bg); border: 1px solid var(--border-color); border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1000;">
                <div class="notif-header" style="padding: 15px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 16px;">Alertes Récentes</h3>
                    <a href="/alerts" style="font-size: 12px; color: var(--teal);">Voir tout l'historique</a>
                </div>
                <div class="notif-list" id="notif-list" style="max-height: 400px; overflow-y: auto;">
                    <div style="text-align: center; padding: 20px; color: var(--text-muted);">Chargement...</div>
                </div>
            </div>
        </div>

        <!-- Son d'alerte caché -->
        <audio id="alert-sound" preload="auto">
            <source src="{{ asset('sounds/alert.mp3') }}" type="audio/mpeg">
        </audio>
    

        <div class="language">

            <button
                type="button"
                class="lang-item lang-active"
            >
                <i class="fa-solid fa-globe"></i>
                <span>
                    {{ app()->getLocale() === 'fr'
                        ? __('messages.french')
                        : __('messages.english')
                    }}
                </span>
                <i class="fa-solid fa-chevron-down"></i>
            </button>

            <div class="lang-dropdown">

                <form action="{{ route('language.change') }}" method="POST">
                    @csrf
                    <input type="hidden" name="language" value="fr">
                    <button type="submit" class="lang-item">Français</button>
                </form>

                <form action="{{ route('language.change') }}" method="POST">
                    @csrf
                    <input type="hidden" name="language" value="en">
                    <button type="submit" class="lang-item">English</button>
                </form>

            </div>

        </div>

        <div class="user-menu">

            <button
                type="button"
                class="user-menu-toggle"
                id="user-menu-toggle"
            >
                <div class="user-avatar-sm user-initials">
                    {{ collect(explode(' ', trim(Auth::user()->name)))
                        ->filter()
                        ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                        ->take(2)
                        ->join('')
                    }}
                </div>
                <i class="fa-solid fa-chevron-down"></i>
            </button>

            <div class="user-dropdown" id="user-dropdown">
                <a href="{{ route('profile') }}" class="user-dropdown-item">
                    <i class="fa-solid fa-user"></i>
                    <span>Mon profil</span>
                </a>
                <a href="{{ route('settings') }}" class="user-dropdown-item">
                    <i class="fa-solid fa-gear"></i>
                    <span>Settings</span>
                </a>
            </div>

        </div>

    </header>
</div>