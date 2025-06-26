<header class="container mx-auto max-w-11/12 py-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <x-app-logo-icon />
            <span class="ml-3 hidden font-semibold text-nowrap md:flex md:text-xl">WFM Geo Toolkit</span>
        </div>
        <flux:navbar {{ $attributes }} class="flex items-center justify-start">
            <flux:navbar.item href="{{ url('/') }}" :current="request()->is('/')">Home</flux:navbar.item>

            @guest
                <flux:dropdown>
                    <flux:navbar.item icon:trailing="chevron-down" :current="request()->routeIs('tools.plotter')">
                        Tools
                    </flux:navbar.item>

                    <flux:menu>
                        <div class="flex flex-col">
                            <flux:menu.group heading="Network">
                                <flux:menu.item
                                    href="{{ route('tools.har-analyzer') }}"
                                    :current="request()->routeIs('tools.har-analyzer')"
                                >
                                    HAR Analyzer
                                </flux:menu.item>
                            </flux:menu.group>
                            <flux:menu.group heading="Mobile">
                                <flux:menu.item
                                    href="{{ route('tools.plotter') }}"
                                    :current="request()->routeIs('tools.plotter')"
                                >
                                    Plotter
                                </flux:menu.item>
                            </flux:menu.group>
                        </div>
                    </flux:menu>
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
