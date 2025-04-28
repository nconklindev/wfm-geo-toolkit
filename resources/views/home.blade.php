<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title>Home | WFM Geo Toolkit</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin />
        <link rel="preload" href="https://fonts.bunny.net/css?family=inter:100,200,300,400,500,600,700,800,900" />
        <link rel="stylesheet" href="https://fonts.bunny.net/css?family=inter:100,200,300,400,500,600,700,800,900" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="flex min-h-screen flex-col bg-zinc-50 text-zinc-900 dark:bg-zinc-900 dark:text-zinc-200">
        <!-- Navigation -->
        <x-navbar-basic />

        <!-- Hero Section -->
        <section class="container mx-auto w-full lg:py-16">
            <div class="flex flex-col items-center lg:flex-row">
                <div class="mb-10 lg:mb-0 lg:w-1/2">
                    <h1
                        class="mb-6 text-4xl leading-tight font-bold text-shadow-lg text-shadow-zinc-300/50 lg:text-5xl dark:text-shadow-lg/30 dark:text-shadow-teal-500"
                    >
                        Known Place Geofence Plotting for Workforce Management
                    </h1>
                    <p class="mb-8 text-lg text-muted">
                        Track, monitor, and optimize your workforce with our advanced geofencing technology. Real-time
                        location tracking with customizable boundaries.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <flux:button variant="primary" href="{{ route('register') }}" class="px-6 py-6">
                            Get Started
                        </flux:button>
                        <flux:button
                            variant="ghost"
                            href="#features"
                            class="border border-zinc-400 px-6 py-6 hover:border-zinc-200"
                        >
                            Learn More
                        </flux:button>
                    </div>
                </div>
                <div class="lg:w-1/2 lg:pl-12">
                    <div class="relative">
                        <div
                            class="overflow-hidden rounded-lg bg-white shadow-lg dark:bg-zinc-900 dark:shadow-[0px_4px_16px_rgba(255,255,255,0.08)]"
                        >
                            <div class="flex items-center space-x-2 bg-zinc-100 p-4 dark:bg-zinc-800">
                                <!-- Mock window circles -->
                                <div class="h-3 w-3 rounded-full bg-danger"></div>
                                <div class="h-3 w-3 rounded-full bg-warning"></div>
                                <div class="h-3 w-3 rounded-full bg-success"></div>
                            </div>
                            <div class="p-6">
                                <div
                                    class="relative h-64 w-full overflow-hidden rounded-md bg-zinc-200 lg:h-80 dark:bg-zinc-800"
                                >
                                    <!-- Map illustration with geofencing -->
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <svg
                                            class="text h-48 w-48 opacity-30 dark:text-zinc-400"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1"
                                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"
                                            />
                                        </svg>
                                    </div>
                                    <!-- Geofence circle -->
                                    <div
                                        class="absolute top-1/2 left-1/2 h-32 w-32 -translate-x-1/2 -translate-y-1/2 transform rounded-full border-2 border-accent opacity-80"
                                    ></div>
                                    <!-- Location pin -->
                                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 transform">
                                        <svg
                                            class="h-8 w-8 text-accent"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                d="M12 0c-4.198 0-8 3.403-8 7.602 0 4.198 3.469 9.21 8 16.398 4.531-7.188 8-12.2 8-16.398 0-4.199-3.801-7.602-8-7.602zm0 11c-1.657 0-3-1.343-3-3s1.343-3 3-3 3 1.343 3 3-1.343 3-3 3z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="container mx-auto mt-10 w-full">
            <div class="mb-16">
                <flux:heading level="2" class="mb-4 text-[2.5rem] font-bold! tracking-tight">
                    The one-stop known places shop.
                </flux:heading>
                <flux:text size="lg" class="max-w-lg">
                    Explore the powerful tools designed to streamline your workforce management and location tracking.
                </flux:text>
            </div>

            <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
                {{-- Changed to 3 columns --}}
                <!-- Feature 1: Plotting (Full Width) -->
                <div
                    class="flex flex-col overflow-hidden rounded-lg border border-zinc-200 bg-zinc-100/50 p-6 shadow-sm transition-shadow hover:shadow-md md:col-span-3 md:flex-row dark:border-zinc-700/50 dark:bg-zinc-800/30"
                    {{-- Span 3 columns --}}
                >
                    <div class="mb-4 w-full md:mr-6 md:mb-0 md:w-1/3 lg:w-1/4">
                        <x-placeholder-pattern
                            class="aspect-video w-full rounded text-zinc-400/70 dark:text-zinc-600/70"
                        />
                    </div>
                    <div class="flex flex-1 flex-col justify-center">
                        <flux:heading level="3" size="lg" class="mb-2 font-semibold text-zinc-900 dark:text-white">
                            Plotting & Visualization
                        </flux:heading>
                        <flux:text variant="default" class="text-zinc-600 dark:text-zinc-400">
                            Visually plot geofence areas against employee punch coordinates on an interactive map for
                            clear spatial analysis and identification of discrepancies.
                        </flux:text>
                    </div>
                </div>

                <!-- Feature 2: Punch Analysis (Wider - 2/3 Width) -->
                <div
                    class="rounded-lg border border-zinc-200 bg-zinc-100/50 p-6 shadow-sm transition-shadow hover:shadow-md md:col-span-2 dark:border-zinc-700/50 dark:bg-zinc-800/30"
                    {{-- Span 2 columns --}}
                >
                    <x-placeholder-pattern class="mb-4 h-32 w-full rounded text-zinc-400/70 dark:text-zinc-600/70" />
                    <flux:heading level="3" size="lg" class="mb-2 font-semibold text-zinc-900 dark:text-white">
                        Punch Analysis
                    </flux:heading>
                    <flux:text variant="default" class="text-zinc-600 dark:text-zinc-400">
                        Diagnose specific punch issues by analyzing individual employee location data against defined
                        geofences.
                    </flux:text>
                </div>

                <!-- Feature 3: Advanced Reporting (Narrower - 1/3 Width) -->
                <div
                    class="rounded-lg border border-zinc-200 bg-zinc-100/50 p-6 shadow-sm transition-shadow hover:shadow-md md:col-span-1 dark:border-zinc-700/50 dark:bg-zinc-800/30"
                    {{-- Span 1 column --}}
                >
                    <x-placeholder-pattern class="mb-4 h-32 w-full rounded text-zinc-400/70 dark:text-zinc-600/70" />
                    <flux:heading level="3" size="lg" class="mb-2 font-semibold text-zinc-900 dark:text-white">
                        Advanced Reporting
                    </flux:heading>
                    <flux:text variant="default" class="text-zinc-600 dark:text-zinc-400">
                        Generate detailed reports comparing punch data with Known Places.
                    </flux:text>
                </div>

                <!-- Feature 4: Automated Alerts (Narrower - 1/3 Width) -->
                <div
                    class="rounded-lg border border-zinc-200 bg-zinc-100/50 p-6 shadow-sm transition-shadow hover:shadow-md md:col-span-1 dark:border-zinc-700/50 dark:bg-zinc-800/30"
                    {{-- Span 1 column --}}
                >
                    <x-placeholder-pattern class="mb-4 h-32 w-full rounded text-zinc-400/70 dark:text-zinc-600/70" />
                    <flux:heading level="3" size="lg" class="mb-2 font-semibold text-zinc-900 dark:text-white">
                        Automated Alerts
                    </flux:heading>
                    <flux:text variant="default" class="text-zinc-600 dark:text-zinc-400">
                        Configure real-time notifications for critical geofence events.
                    </flux:text>
                </div>

                <!-- Feature 5: Privacy Controls (Wider - 2/3 Width) -->
                <div
                    class="rounded-lg border border-zinc-200 bg-zinc-100/50 p-6 shadow-sm transition-shadow hover:shadow-md md:col-span-2 dark:border-zinc-700/50 dark:bg-zinc-800/30"
                    {{-- Span 2 columns --}}
                >
                    <x-placeholder-pattern class="mb-4 h-32 w-full rounded text-zinc-400/70 dark:text-zinc-600/70" />
                    <flux:heading level="3" size="lg" class="mb-2 font-semibold text-zinc-900 dark:text-white">
                        Privacy Controls
                    </flux:heading>
                    <flux:text variant="default" class="text-zinc-600 dark:text-zinc-400">
                        Ensure employee privacy with granular controls, limiting tracking to specific work hours and
                        locations. Respect for privacy is paramount.
                    </flux:text>
                </div>

                <!-- Feature 6: Import Known Places (Full Width) -->
                <div
                    class="flex flex-col overflow-hidden rounded-lg border border-zinc-200 bg-zinc-100/50 p-6 shadow-sm transition-shadow hover:shadow-md md:col-span-3 md:flex-row dark:border-zinc-700/50 dark:bg-zinc-800/30"
                    {{-- Span 3 columns --}}
                >
                    <div class="flex flex-1 flex-col justify-center">
                        <flux:heading level="3" size="lg" class="mb-2 font-semibold text-zinc-900 dark:text-white">
                            Import Known Places
                        </flux:heading>
                        <flux:text variant="default" class="text-zinc-600 dark:text-zinc-400">
                            Seamlessly import your existing location data via the Pro WFM API for rapid setup and
                            updates, ensuring consistency across systems.
                        </flux:text>
                    </div>
                    <div class="mb-4 w-full md:mr-6 md:mb-0 md:w-1/3 lg:w-1/4">
                        <x-placeholder-pattern
                            class="aspect-video w-full rounded text-zinc-400/70 dark:text-zinc-600/70"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->

        <!-- Footer -->
        @include('partials.footer')
        @fluxScripts
    </body>
</html>
