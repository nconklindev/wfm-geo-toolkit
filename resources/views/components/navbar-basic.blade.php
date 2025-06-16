<header class="container mx-auto max-w-6xl py-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <x-app-logo-icon />
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
