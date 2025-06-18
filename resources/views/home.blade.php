<x-layouts.guest :title="__('WFM Geo Toolkit')">
    <!-- Hero Section -->
    <section class="lg:py-16">
        <div class="flex flex-col items-center lg:flex-row">
            <div class="mb-10 lg:mb-0 lg:w-1/2">
                <flux:heading
                    level="1"
                    size="xl"
                    class="mb-6 leading-tight font-bold! text-shadow-md md:text-4xl lg:text-5xl"
                >
                    Known Place Geofence Plotting for Workforce Management
                </flux:heading>
                <flux:text class="mb-8 text-base md:text-lg">
                    Track, monitor, and optimize your workforce with our advanced geofencing technology. Real-time
                    location tracking with customizable boundaries.
                </flux:text>
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
            <!-- Browser render window -->
            <div class="w-full md:w-1/2 md:pl-12">
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
    <section id="features" class="mt-10 w-full">
        <div class="mb-16">
            <flux:heading level="2" class="mb-4 text-4xl! tracking-tight">
                The one-stop
                <span class="block text-teal-700 dark:text-teal-500">known places shop.</span>
            </flux:heading>
            <flux:text class="text-base md:text-lg">
                Explore the powerful tools designed to streamline your workforce management and location tracking.
            </flux:text>
        </div>

        <div class="grid grid-cols-1 gap-10 md:grid-cols-3">
            <!-- Feature 1: Plotting (Full Width) -->
            <div
                class="flex flex-col overflow-hidden rounded-lg border border-zinc-200 bg-zinc-100/50 p-6 shadow-sm transition-shadow hover:shadow-md md:col-span-3 md:flex-row dark:border-zinc-700/50 dark:bg-zinc-800/30"
                {{-- Span 3 columns --}}
            >
                <div class="mr-4 mb-4 md:mb-0">
                    <flux:icon.map-pin class="mb-2 size-28 text-center" />
                </div>
                <div class="flex flex-1 flex-col justify-center">
                    <flux:heading level="3" size="lg" class="mb-2 font-semibold text-zinc-900 dark:text-white">
                        Plotting & Visualization
                    </flux:heading>
                    <flux:text>
                        Visually plot geofence areas against employee punch coordinates on an interactive map for clear
                        spatial analysis and identification of discrepancies.
                    </flux:text>
                </div>
            </div>

            <!-- Feature 2: Punch Analysis (Wider - 2/3 Width) -->
            <div
                class="flex flex-col rounded-lg border border-zinc-200 bg-zinc-100/50 p-6 shadow-sm transition-shadow hover:shadow-md dark:border-zinc-700/50 dark:bg-zinc-800/30"
                {{-- Span 2 columns --}}
            >
                <flux:icon.magnifying-glass-circle class="mb-2 size-28" />
                <div class="flex-start">
                    <flux:heading level="3" size="lg" class="mb-2 font-semibold text-zinc-900 dark:text-white">
                        Punch Analysis
                    </flux:heading>
                    <flux:text>
                        Diagnose specific punch issues by analyzing individual employee location data against defined
                        geofences.
                    </flux:text>
                </div>
            </div>

            <!-- Feature 4: Automated Alerts (Narrower - 1/3 Width) -->
            <div
                class="flex flex-col rounded-lg border border-zinc-200 bg-zinc-100/50 p-6 shadow-sm transition-shadow hover:shadow-md dark:border-zinc-700/50 dark:bg-zinc-800/30"
                {{-- Span 1 column --}}
            >
                <flux:icon.bell-alert class="mb-2 size-28" />
                <div class="flex-start">
                    <flux:heading level="3" size="lg" class="mb-2 font-semibold text-zinc-900 dark:text-white">
                        Automated Alerts
                    </flux:heading>
                    <flux:text>Receive real-time notifications for critical geofence events.</flux:text>
                </div>
            </div>

            <!-- Feature 6: Import Known Places (Full Width) -->
            <div
                class="flex flex-col overflow-hidden rounded-lg border border-zinc-200 bg-zinc-100/50 p-6 shadow-sm transition-shadow hover:shadow-md dark:border-zinc-700/50 dark:bg-zinc-800/30"
                {{-- Span 3 columns --}}
            >
                <flux:icon.arrow-up-on-square class="mb-2 size-28" />
                <div class="flex-start">
                    <flux:heading level="3" size="lg" class="mb-2 font-semibold text-zinc-900 dark:text-white">
                        Import Known Places
                    </flux:heading>
                    <flux:text>
                        Seamlessly import your existing location data via the Pro WFM API for rapid setup and updates,
                        ensuring consistency across systems.
                    </flux:text>
                </div>
            </div>
        </div>
    </section>
</x-layouts.guest>
