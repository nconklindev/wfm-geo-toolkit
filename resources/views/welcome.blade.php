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
        <section class="mx-auto w-full max-w-7xl px-6 py-12 lg:px-8 lg:py-24">
            <div class="flex flex-col items-center lg:flex-row">
                <div class="mb-10 lg:mb-0 lg:w-1/2">
                    <h1 class="mb-6 text-4xl leading-tight font-bold lg:text-5xl">
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
        <section
            id="features"
            class="mx-auto w-full max-w-7xl rounded-lg bg-white px-6 py-16 shadow-sm lg:px-8 dark:bg-zinc-800 dark:shadow-[0px_4px_16px_rgba(255,255,255,0.05)]"
        >
            <div class="mb-16 text-center">
                <h2 class="mb-4 text-3xl font-bold">Key Features</h2>
                <p class="mx-auto max-w-3xl text-lg text-muted">
                    Our geofencing solution provides powerful tools to manage your workforce effectively and
                    efficiently.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <!-- Feature 1 -->
                <div
                    class="rounded-lg border border-zinc-200 p-6 transition-all hover:border-zinc-900 dark:border-zinc-700 dark:hover:border-zinc-500"
                >
                    <div
                        class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-900"
                    >
                        <svg
                            class="h-6 w-6 text-accent"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"
                            />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold">Plot Known Places & Punches</h3>
                    <p class="text-muted">
                        Plot areas based on configured Known Places and geolocation coordinates from employee punches.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div
                    class="rounded-lg border border-zinc-200 p-6 transition-all hover:border-zinc-900 dark:border-zinc-700 dark:hover:border-zinc-500"
                >
                    <div
                        class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-900"
                    >
                        <svg
                            class="h-6 w-6 text-accent"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"
                            />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold">Punch Troubleshooter</h3>
                    <p class="text-muted">
                        Create individual test scenarios to diagnose why specific employees are experiencing geolocation
                        punch problems at certain locations.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div
                    class="rounded-lg border border-zinc-200 p-6 transition-all hover:border-zinc-900 dark:border-zinc-700 dark:hover:border-zinc-500"
                >
                    <div
                        class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-900"
                    >
                        <svg
                            class="h-6 w-6 text-accent"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                            />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold">Advanced Reporting</h3>
                    <p class="text-muted">
                        Create comprehensive analyses of punch location data versus Known Places to optimize geofence
                        configurations and improve location validation.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div
                    class="rounded-lg border border-zinc-200 p-6 transition-all hover:border-zinc-900 dark:border-zinc-700 dark:hover:border-zinc-500"
                >
                    <div
                        class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-900"
                    >
                        <svg
                            class="h-6 w-6 text-accent"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                            />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold">Automated Alerts</h3>
                    <p class="text-muted">
                        Set up customizable notifications for geofence events, including entry, exit, and dwell time
                        violations.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div
                    class="rounded-lg border border-zinc-200 p-6 transition-all hover:border-zinc-900 dark:border-zinc-700 dark:hover:border-zinc-500"
                >
                    <div
                        class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-900"
                    >
                        <svg
                            class="h-6 w-6 text-accent"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                            />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold">Privacy Controls</h3>
                    <p class="text-muted">
                        Robust privacy settings allowing you to track only during work hours and in designated areas,
                        respecting employee privacy.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div
                    class="rounded-lg border border-zinc-200 p-6 transition-all hover:border-zinc-900 dark:border-zinc-700 dark:hover:border-zinc-500"
                >
                    <div
                        class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-900"
                    >
                        <svg
                            class="h-6 w-6 text-accent"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"
                            />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold">Import Known Places</h3>
                    <p class="text-muted">
                        Easily import configured Known Places using the existing Pro WFM API to quickly set up or update
                        your location database.
                    </p>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="mx-auto w-full max-w-7xl px-6 py-16 lg:px-8">
            <!-- TODO: Add something -->
        </section>

        <!-- Footer -->
        @include('partials.footer')
        @fluxScripts
    </body>
</html>
