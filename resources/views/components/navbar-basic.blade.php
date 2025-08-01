<header class="sticky top-0 z-999 border-b border-zinc-200/80 bg-white/80 backdrop-blur-md dark:border-zinc-800/80 dark:bg-zinc-900/80">
    <div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <!-- Logo Section -->
            <div class="flex items-center space-x-3">
                <x-app-logo-icon class="h-8 w-8" />
                <span class="text-xl font-bold text-zinc-900 dark:text-white">WFM Toolkit</span>
            </div>

            <!-- Desktop Navigation -->
            <nav class="hidden items-center space-x-1 md:flex">
                <!-- Home Link -->
                <a href="{{ url('/') }}"
                   class="rounded-lg px-3 py-2 text-sm font-medium transition-colors duration-200 {{ request()->is('/') ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
                    Home
                </a>

                <!-- Tools Mega Menu -->
                <div class="relative" x-data="{ open: false }" @click.away="open = false" @keydown.escape="open = false">
                    <button @click="open = !open"
                            class="flex items-center space-x-1 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200 {{ request()->routeIs('tools.*') ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white' }}"
                            :class="{ 'ring-2 ring-sky-500/20': open }">
                        <span>Tools</span>
                        <svg class="h-4 w-4 transition-all duration-300" :class="{ 'rotate-180 text-sky-500': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Mega Menu Dropdown -->
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                         class="absolute -left-20 mt-3 w-[28rem] origin-top rounded-2xl border border-zinc-200/60 bg-white/95 backdrop-blur-xl shadow-2xl ring-1 ring-zinc-900/5 dark:border-zinc-700/60 dark:bg-zinc-800/95 dark:ring-white/10"
                         style="display: none;">

                        <!-- Header with sky gradient -->
                        <div class="relative overflow-hidden rounded-t-2xl bg-gradient-to-r from-sky-500 via-sky-600 to-cyan-500 p-4">
                            <div class="relative z-10">
                                <h3 class="text-lg font-bold text-white">Toolkit</h3>
                                <p class="text-sky-100">Essential utilities for your workflow</p>
                            </div>
                            <!-- Decorative elements -->
                            <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-white/10"></div>
                            <div class="absolute -left-2 -bottom-2 h-12 w-12 rounded-full bg-white/5"></div>
                        </div>

                        <!-- Tools Content -->
                        <div class="p-4 space-y-4">
                            <!-- API Tools Section -->
                            <div>
                                <div class="flex items-center space-x-2 mb-3">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-md bg-sky-100 dark:bg-sky-900/30">
                                        <flux:icon.command-line class="size-4" />
                                    </div>
                                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">API Tools</h4>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <!-- API Explorer Card -->
                                    <a href="{{ route('tools.api-explorer') }}"
                                       class="group relative overflow-hidden rounded-lg border border-zinc-200/50 bg-gradient-to-br from-sky-50 to-sky-100 p-3 transition-all duration-300 hover:scale-[1.02] hover:shadow-md hover:shadow-sky-500/10 dark:border-zinc-700/50 dark:from-sky-900/20 dark:to-sky-800/20 {{ request()->routeIs('tools.api-explorer') ? 'ring-2 ring-sky-500/30' : '' }}">
                                        <div class="flex items-center space-x-2">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-md bg-sky-500 text-white shadow-sm">
                                                <flux:icon.command-line class="size-5" />
                                            </div>
                                            <div class="min-w-0">
                                                <h5 class="font-medium text-zinc-900 group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-400 text-sm">API Explorer</h5>
                                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Test endpoints</flux:text>
                                            </div>
                                        </div>
                                        <div class="absolute inset-0 bg-gradient-to-r from-sky-500/0 to-sky-500/5 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                                    </a>

                                    <!-- API Docs Card -->
                                    <a href="{{ route('tools.api-explorer-docs') }}"
                                       class="group relative overflow-hidden rounded-lg border border-zinc-200/50 bg-gradient-to-br from-sky-50 to-sky-100 p-3 transition-all duration-300 hover:scale-[1.02] hover:shadow-md hover:shadow-sky-500/10 dark:border-zinc-700/50 dark:from-sky-900/20 dark:to-sky-800/20 {{ request()->routeIs('tools.api-explorer-docs') ? 'ring-2 ring-sky-500/30' : '' }}">
                                        <div class="flex items-center space-x-2">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-md bg-sky-600 text-white shadow-sm">
                                                <flux:icon.book-open class="size-5" />
                                            </div>
                                            <div class="min-w-0">
                                                <h5 class="font-medium text-zinc-900 group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-400 text-sm">Guide</h5>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Documentation</p>
                                            </div>
                                        </div>
                                        <div class="absolute inset-0 bg-gradient-to-r from-sky-500/0 to-sky-500/5 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                                    </a>
                                </div>
                            </div>

                            <!-- Mobile Tools Section -->
                            <div>
                                <div class="flex items-center space-x-2 mb-3">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-md bg-slate-100 dark:bg-slate-900/30">
                                        <flux:icon.device-phone-mobile class="size-4" />
                                    </div>
                                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">Mobile Tools</h4>
                                </div>
                                <div class="grid grid-cols-1 gap-2">
                                    <!-- Plotter Card -->
                                    <a href="{{ route('tools.plotter') }}"
                                       class="group relative overflow-hidden rounded-lg border border-zinc-200/50 bg-gradient-to-br from-slate-50 to-stone-50 p-3 transition-all duration-300 hover:scale-[1.02] hover:shadow-md hover:shadow-slate-500/10 dark:border-zinc-700/50 dark:from-slate-900/20 dark:to-stone-900/20 {{ request()->routeIs('tools.plotter') ? 'ring-2 ring-slate-500/30' : '' }}">
                                        <div class="flex items-center space-x-2">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-md bg-slate-600 text-white shadow-sm">
                                                <flux:icon.map-pin class="size-5" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <h5 class="font-medium text-zinc-900 group-hover:text-slate-600 dark:text-white dark:group-hover:text-slate-400 text-sm">Plotter</h5>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Mobile data visualization and mapping</p>
                                            </div>
                                        </div>
                                        <div class="absolute inset-0 bg-gradient-to-r from-slate-500/0 to-slate-500/5 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                                    </a>
                                </div>
                            </div>

                            <!-- Network Tools Section -->
                            <div>
                                <div class="flex items-center space-x-2 mb-3">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-md bg-emerald-100 dark:bg-emerald-900/30">
                                        <flux:icon.globe-alt class="size-4" />
                                    </div>
                                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">Network Tools</h4>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <!-- HAR Analyzer Card -->
                                    <a href="{{ route('tools.har-analyzer') }}"
                                       class="group relative overflow-hidden rounded-lg border border-zinc-200/50 bg-gradient-to-br from-emerald-50 to-teal-50 p-3 transition-all duration-300 hover:scale-[1.02] hover:shadow-md hover:shadow-emerald-500/10 dark:border-zinc-700/50 dark:from-emerald-900/20 dark:to-teal-900/20 {{ request()->routeIs('tools.har-analyzer') ? 'ring-2 ring-emerald-500/30' : '' }}">
                                        <div class="flex items-center space-x-2">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-md bg-emerald-600 text-white shadow-sm">
                                                <flux:icon.signal-slash class="size-5" />
                                            </div>
                                            <div class="min-w-0">
                                                <h5 class="font-medium text-zinc-900 group-hover:text-emerald-600 dark:text-white dark:group-hover:text-emerald-400 text-sm">HAR Analyzer</h5>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Network analysis</p>
                                            </div>
                                        </div>
                                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/0 to-emerald-500/5 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                                    </a>

                                    @guest
                                        <!-- IP Checker Card (Guest only) -->
                                        <a href="{{ route('tools.ip-checker-import') }}"
                                           class="group relative overflow-hidden rounded-lg border border-zinc-200/50 bg-gradient-to-br from-emerald-50 to-teal-50 p-3 transition-all duration-300 hover:scale-[1.02] hover:shadow-md hover:shadow-emerald-500/10 dark:border-zinc-700/50 dark:from-emerald-900/20 dark:to-teal-900/20 {{ request()->routeIs('tools.ip-checker-import') ? 'ring-2 ring-emerald-500/30' : '' }}">
                                            <div class="flex items-center space-x-2">
                                                <div class="flex h-8 w-8 items-center justify-center rounded-md bg-teal-600 text-white shadow-sm">
                                                    <flux:icon.network class="size-5" />
                                                </div>
                                                <div class="min-w-0">
                                                    <h5 class="font-medium text-zinc-900 group-hover:text-teal-600 dark:text-white dark:group-hover:text-teal-400 text-sm">IP Checker</h5>
                                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Address validation</p>
                                                </div>
                                            </div>
                                            <div class="absolute inset-0 bg-gradient-to-r from-teal-500/0 to-teal-500/5 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                                        </a>
                                    @endguest
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Auth-specific Navigation -->
                @auth
                    <a href="{{ url('/dashboard') }}"
                       class="rounded-lg px-3 py-2 text-sm font-medium transition-colors duration-200 {{ request()->is('dashboard') ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="rounded-lg px-3 py-2 text-sm font-medium transition-colors duration-200 text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white">
                        Log in
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors duration-200 hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100">
                            Register
                        </a>
                    @endif
                @endauth
            </nav>

            <!-- Right Side Actions -->
            <div class="flex items-center space-x-3">
                <!-- GitHub Link -->
                <a href="https://github.com/nconklindev/wfm-geo-toolkit"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="flex h-9 w-9 items-center justify-center rounded-lg text-zinc-600 transition-colors duration-200 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                </a>

                <!-- Dark Mode Toggle -->
                <button
                    id="dark-mode-toggle"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-zinc-600 transition-colors duration-200 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white"
                    aria-label="Toggle dark mode"
                    x-model="$flux.appearance"
                >
                    <!-- Sun icon (visible in dark mode) -->
                    <svg class="hidden h-5 w-5 dark:block" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <!-- Moon icon (visible in light mode) -->
                    <svg class="block h-5 w-5 dark:hidden" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <!-- Mobile Menu Toggle - CSS Only -->
                <div class="md:hidden">
                    <!-- Hidden checkbox that controls the menu state -->
                    <input type="checkbox" id="mobile-menu-toggle" class="peer hidden" />

                    <!-- Toggle button -->
                    <label for="mobile-menu-toggle"
                           class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg text-zinc-600 transition-colors duration-200 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white">
                        <svg class="h-5 w-5" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </label>

                    <!-- Mobile Navigation - shown when checkbox is checked -->
                    <div class="peer-checked:block hidden absolute left-0 right-0 top-16 border-t border-zinc-200 bg-white/95 py-4 backdrop-blur-md dark:border-zinc-700 dark:bg-zinc-900/95">
                        <div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                            <nav class="flex flex-col space-y-1">
                                <!-- Home Link -->
                                <a href="{{ url('/') }}"
                                   class="rounded-lg px-3 py-2 text-base font-medium transition-colors duration-200 {{ request()->is('/') ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
                                    Home
                                </a>

                                <!-- Tools Section -->
                                <div class="py-2">
                                    <p class="px-3 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Tools</p>
                                    <div class="mt-2 space-y-1">
                                        <!-- API Group -->
                                        <p class="px-3 py-1 text-xs font-medium text-zinc-400 dark:text-zinc-500">API</p>
                                        <a href="{{ route('tools.api-explorer') }}"
                                           class="block rounded-lg px-6 py-2 text-sm transition-colors duration-200 {{ request()->routeIs('tools.api-explorer') ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
                                            API Explorer
                                        </a>
                                        <a href="{{ route('tools.api-explorer-docs') }}"
                                           class="block rounded-lg px-6 py-2 text-sm transition-colors duration-200 {{ request()->routeIs('tools.api-explorer-docs') ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
                                            Guide
                                        </a>

                                        <!-- Mobile Group -->
                                        <p class="px-3 py-1 text-xs font-medium text-zinc-400 dark:text-zinc-500">Mobile</p>
                                        <a href="{{ route('tools.plotter') }}"
                                           class="block rounded-lg px-6 py-2 text-sm transition-colors duration-200 {{ request()->routeIs('tools.plotter') ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
                                            Plotter
                                        </a>

                                        <!-- Network Group -->
                                        <p class="px-3 py-1 text-xs font-medium text-zinc-400 dark:text-zinc-500">Network</p>
                                        <a href="{{ route('tools.har-analyzer') }}"
                                           class="block rounded-lg px-6 py-2 text-sm transition-colors duration-200 {{ request()->routeIs('tools.har-analyzer') ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
                                            HAR Analyzer
                                        </a>
                                        @guest
                                            <a href="{{ route('tools.ip-checker-import') }}"
                                               class="block rounded-lg px-6 py-2 text-sm transition-colors duration-200 {{ request()->routeIs('tools.ip-checker-import') ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
                                                IP Address Checker
                                            </a>
                                        @endguest
                                    </div>
                                </div>

                                <!-- Auth Links -->
                                @auth
                                    <a href="{{ url('/dashboard') }}"
                                       class="rounded-lg px-3 py-2 text-base font-medium transition-colors duration-200 {{ request()->is('dashboard') ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
                                        Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('login') }}"
                                       class="rounded-lg px-3 py-2 text-base font-medium transition-colors duration-200 text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white">
                                        Log in
                                    </a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}"
                                           class="rounded-lg bg-zinc-900 px-3 py-2 text-base font-medium text-white transition-colors duration-200 hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100">
                                            Register
                                        </a>
                                    @endif
                                @endauth
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Dark mode toggle functionality
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        const html = document.documentElement;

        const savedTheme = localStorage.getItem('theme' || 'flux.appearance');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }

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

        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
            if (!localStorage.getItem('theme' || 'flux.appearance')) {
                if (e.matches) {
                    html.classList.add('dark');
                } else {
                    html.classList.remove('dark');
                }
            }
        });

        // Close mobile menu on window resize (CSS-only approach doesn't need this but it's good UX)
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 768) {
                document.getElementById('mobile-menu-toggle').checked = false;
            }
        });
    });
</script>
