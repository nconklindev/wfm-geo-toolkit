<header class="container mx-auto w-full py-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <svg
                class="h-10 w-10 text-accent"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                />
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                />
            </svg>
            <span class="ml-3 text-xl font-semibold">WFM Geo Toolkit</span>
        </div>
        <flux:navbar {{ $attributes }}>
            <flux:navbar.item href="{{ url('/') }}" :current="request()->is('/')">Home</flux:navbar.item>

            @guest
                <flux:dropdown>
                    <flux:navbar.item icon:trailing="chevron-down" :current="request()->routeIs('tools.plotter')">
                        Tools
                    </flux:navbar.item>

                    <flux:navmenu>
                        <flux:navmenu.item
                            href="{{ route('tools.plotter') }}"
                            :current="request()->routeIs('tools.plotter')"
                        >
                            Plotter
                        </flux:navmenu.item>
                    </flux:navmenu>
                </flux:dropdown>
            @endguest

            @auth
                <flux:navbar.item href="{{ url('/dashboard') }}">Dashboard</flux:navbar.item>
            @else
                <flux:navbar.item href="{{ route('login') }}">Log in</flux:navbar.item>

                @if (Route::has('register'))
                    <flux:navbar.item href="{{ route('register') }}">Register</flux:navbar.item>
                @endif
            @endauth
        </flux:navbar>
    </div>
</header>
