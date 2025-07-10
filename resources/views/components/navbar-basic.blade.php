<header class="container mx-auto max-w-11/12 py-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <x-app-logo-icon />
            <span class="ml-3 hidden font-semibold text-nowrap md:flex md:text-xl">WFM Toolkit</span>
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
                            <flux:menu.group heading="API">
                                <flux:menu.item
                                    href="{{ route('tools.api-explorer') }}"
                                    :current="request()->routeIs('tools.api-explorer')"
                                >
                                    API Explorer
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
                            <flux:menu.group heading="Network">
                                <flux:menu.item
                                    href="{{ route('tools.har-analyzer') }}"
                                    :current="request()->routeIs('tools.har-analyzer')"
                                >
                                    HAR Analyzer
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
                <flux:navbar.item href="{{ url('/dashboard') }}">Dashboard</flux:navbar.item>
            @else
                <flux:navbar.item href="{{ route('login') }}">Log in</flux:navbar.item>

                @if (Route::has('register'))
                    <flux:navbar.item href="{{ route('register') }}">Register</flux:navbar.item>
                @endif
            @endauth

            <!-- Dark Mode Toggle -->
            <div class="ml-4 items-center justify-center">
                <button
                    id="dark-mode-toggle"
                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 transition-colors duration-200 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700"
                    aria-label="Toggle dark mode"
                    x-model="$flux.appearance"
                >
                    <!-- Sun icon (visible in dark mode) -->
                    <svg
                        id="sun-icon"
                        class="hidden h-5 w-5 text-zinc-900 dark:block dark:text-zinc-100"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                        />
                    </svg>
                    <!-- Moon icon (visible in light mode) -->
                    <svg
                        id="moon-icon"
                        class="block h-5 w-5 text-zinc-900 dark:hidden dark:text-zinc-100"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                        />
                    </svg>
                </button>
            </div>
        </flux:navbar>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        const html = document.documentElement;

        // Check for saved theme preference or default to system preference
        // Flux uses the local storage variable called flux.appearance
        // so we have to check for that too
        const savedTheme = localStorage.getItem('theme' || 'flux.appearance');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }

        // Toggle dark mode
        darkModeToggle.addEventListener('click', function () {
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
                localStorage.setItem('flux.appearance', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
                localStorage.setItem('flux.appearance', 'dark');
            }
        });

        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
            if (!localStorage.getItem('theme' || 'flux.appearance')) {
                if (e.matches) {
                    html.classList.add('dark');
                } else {
                    html.classList.remove('dark');
                }
            }
        });
    });
</script>
