
<div class="main-area">

    <header class="topbar">
        
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


        
        <button
            type="button"
            class="icon-btn"
            id="date-range-toggle"
        >
            <i class="fa-regular fa-sun"></i>
            <span id="date-range-label">
                {{ now()->subDays(6)->translatedFormat('d M.') }}
                -
                {{ now()->translatedFormat('d M. Y') }}
            </span>
            <i class="fa-solid fa-chevron-down"></i>
        </button>

        <div
            class="date-range-menu"
            id="date-range-menu"
        >

            <button type="button" data-range="today">
                Aujourd'hui
            </button>

            <button type="button" data-range="7">
                7 derniers jours
            </button>

            <button type="button" data-range="30">
                30 derniers jours
            </button>

            <button type="button" data-range="90">
                90 derniers jours
            </button>

            <button type="button" data-range="custom">
                <i class="fa-regular fa-calendar"></i>
                Période personnalisée
            </button>

        </div>        

        <button class="icon-btn notif-btn">
            <i class="fa-regular fa-bell"></i>
            <span class="notif-badge">6</span>
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

            <form
                action="{{ route('language.change') }}"
                method="POST"
            >
                @csrf

                <input
                    type="hidden"
                    name="language"
                    value="fr"
                >

                <button
                    type="submit"
                    class="lang-item"
                >
                    Français
                </button>

            </form>


            <form
                action="{{ route('language.change') }}"
                method="POST"
            >
                @csrf

                <input
                    type="hidden"
                    name="language"
                    value="en"
                >

                <button
                    type="submit"
                    class="lang-item"
                >
                    English
                </button>

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


            {{-- Menu utilisateur --}}
            <div
                class="user-dropdown"
                id="user-dropdown"
            >

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