<x-layouts.guest :title="__('Home')">
    <!-- Hero Section -->
    <section class="lg:py-16">
        <div class="flex flex-col items-center lg:flex-row">
            <div class="mb-10 lg:mb-0 lg:w-1/2">
                <flux:heading
                    level="1"
                    size="xl"
                    class="mb-6 leading-tight font-bold! text-shadow-md md:text-4xl lg:text-5xl"
                >
                    The Complete WFM Toolkit
                </flux:heading>
                <flux:text class="mb-8 text-base md:text-lg">
                    Your comprehensive solution for workforce management, geofencing, API exploration, and network
                    analysis. Everything you need in one powerful platform.
                </flux:text>
                <div class="flex flex-wrap gap-4">
                    <flux:button variant="primary" href="#tools" class="px-6 py-6">Try Tools Now</flux:button>
                    <flux:button
                        variant="ghost"
                        href="{{ route('register') }}"
                        class="border border-zinc-400 px-6 py-6 hover:border-zinc-200"
                    >
                        Create Account
                    </flux:button>
                </div>
            </div>
            <!-- Tool Preview -->
            <div class="w-full md:w-1/2 md:pl-12">
                <div class="relative">
                    <div
                        class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-zinc-900 dark:shadow-md dark:shadow-zinc-200/5"
                    >
                        <div class="flex items-center space-x-2 bg-zinc-100 p-4 dark:bg-zinc-800">
                            <!-- Mock window circles -->
                            <div class="h-3 w-3 rounded-full bg-red-600"></div>
                            <div class="h-3 w-3 rounded-full bg-amber-600"></div>
                            <div class="h-3 w-3 rounded-full bg-green-600"></div>
                            <div class="ml-4 text-sm text-zinc-600 dark:text-zinc-400">API Explorer</div>
                        </div>
                        <div class="p-6">
                            <div
                                class="relative h-64 w-full overflow-hidden rounded-md bg-gradient-to-br from-slate-50 to-slate-100 lg:h-80 dark:from-zinc-800 dark:to-zinc-900"
                            >
                                <!-- API Explorer mockup -->
                                <div class="absolute inset-0 p-4 font-mono text-xs">
                                    <!-- URL bar -->
                                    <div class="mb-3 flex items-center rounded bg-white p-2 shadow-sm dark:bg-zinc-800">
                                        <span class="mr-2 rounded bg-green-600 px-2 py-1 text-white">GET</span>
                                        <span class="text-zinc-600 dark:text-zinc-400">/api/v1/persons/employees</span>
                                    </div>
                                    <!-- Response -->
                                    <div class="rounded bg-zinc-900 p-3 text-green-400">
                                        <div>{"status": "success",</div>
                                        <div class="ml-2">"data": [</div>
                                        <div class="ml-4">{"id": 123,</div>
                                        <div class="ml-4">"name": "John Doe",</div>
                                        <div class="ml-4">"location": {...}}</div>
                                        <div class="ml-2">]</div>
                                        <div>}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tools Section -->
    <section id="tools" class="mt-16 w-full">
        <div class="mb-12 text-center">
            <flux:heading level="2" class="mb-4 text-3xl! tracking-tight">
                Try Our Tools
                <span class="block text-teal-700 dark:text-teal-500">No signup required</span>
            </flux:heading>
            <flux:text class="text-base md:text-lg">
                Pro WFM tools that complement your daily workflow. Available instantly in your browser. No downloads
                required.
            </flux:text>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-1 lg:grid-cols-1">
            <!-- Plotter Tool -->
            <div
                class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
            >
                <div class="p-6">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-lg bg-teal-100 dark:bg-teal-900"
                            >
                                <flux:icon.map class="h-6 w-6 text-teal-600 dark:text-teal-400" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <flux:heading level="3" size="lg" class="mb-2 font-semibold">
                                Geofencing Punch Plotting Tool
                            </flux:heading>
                            <flux:text class="mb-4 text-sm">
                                Visualize employee punch data against geofenced areas. Use real punch data and Known
                                Places to see exactly where employees are clocking in and identify potential issues.
                            </flux:text>
                            <div class="mb-4 flex flex-wrap gap-2">
                                <flux:badge variant="pill" color="teal" size="sm">Geofencing</flux:badge>
                                <flux:badge variant="pill" color="sky" size="sm">Data Visualization</flux:badge>
                                <flux:badge variant="pill" color="green" size="sm">Mobile</flux:badge>
                            </div>
                            <flux:button href="{{ route('tools.plotter') }}" size="sm" icon:trailing="arrow-right">
                                Plot punches
                            </flux:button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- API Explorer Tool -->
            <div
                class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
            >
                <div class="p-6">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900"
                            >
                                <flux:icon.code-bracket class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <flux:heading level="3" size="lg" class="mb-2 font-semibold">
                                Pro WFM API Explorer
                            </flux:heading>
                            <flux:text class="mb-4 text-sm">
                                Interactive API testing tool for many Pro WFM endpoints. Test authentication, explore
                                available endpoints, and see real API responses. Perfect for system admins and
                                timekeepers alike. We take the guesswork out of API testing.
                                <strong>Postman not required!</strong>
                            </flux:text>
                            <div class="mb-4 flex flex-wrap gap-2">
                                <flux:badge variant="pill" size="sm" color="violet">API Testing</flux:badge>
                                <flux:badge variant="pill" size="sm" color="indigo">Integration</flux:badge>
                                <flux:badge variant="pill" size="sm" color="fuchsia">Development</flux:badge>
                            </div>
                            <flux:button
                                href="{{ route('tools.api-explorer') }}"
                                size="sm"
                                icon:trailing="arrow-right"
                            >
                                Call your first API
                            </flux:button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HAR Analyzer Tool -->
            <div
                class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
            >
                <div class="p-6">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900"
                            >
                                <flux:icon.chart-bar class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <flux:heading level="3" size="lg" class="mb-2 font-semibold">
                                HAR File Analyzer
                            </flux:heading>
                            <flux:text class="mb-4 text-sm">
                                Analyze network traffic with the HAR Analyzer. Upload HAR files to identify performance
                                issues, failed requests, and connectivity problems with Pro WFM applications.
                                Specifically tuned to help you see what might be causing that pesky 500 error.
                            </flux:text>
                            <div class="mb-4 flex flex-wrap gap-2">
                                <flux:badge variant="pill" size="sm" color="green">Network Analysis</flux:badge>
                                <flux:badge variant="pill" size="sm" color="amber">Performance</flux:badge>
                                <flux:badge variant="pill" size="sm" color="red">Troubleshooting</flux:badge>
                            </div>
                            <flux:button
                                href="{{ route('tools.har-analyzer') }}"
                                size="sm"
                                icon:trailing="arrow-right"
                            >
                                Analyze now
                            </flux:button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="mt-16 w-full">
        <div class="mb-12 text-center">
            <flux:heading level="2" class="mb-4 text-3xl! tracking-tight">Why Use WFM Toolkit?</flux:heading>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="text-center">
                <div
                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-teal-100 dark:bg-teal-900"
                >
                    <flux:icon.bolt class="h-8 w-8 text-teal-600 dark:text-teal-400" />
                </div>
                <flux:heading level="3" size="lg" class="mb-2 font-semibold">Instant Access</flux:heading>
                <flux:text class="text-sm">
                    No downloads, no installation. All tools work directly in your browser with instant access.
                </flux:text>
            </div>

            <div class="text-center">
                <div
                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900"
                >
                    <flux:icon.hand-thumb-up class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                </div>
                <flux:heading level="3" size="lg" class="mb-2 font-semibold">Easy to Use</flux:heading>
                <flux:text class="text-sm">
                    We developed our tools to be intuitive and easy to use. No account required. Just pick from our
                    selection of tools, and get started.
                </flux:text>
            </div>

            <div class="text-center">
                <div
                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900"
                >
                    <flux:icon.users class="h-8 w-8 text-green-600 dark:text-green-400" />
                </div>
                <flux:heading level="3" size="lg" class="mb-2 font-semibold">Pro WFM Focused</flux:heading>
                <flux:text class="text-sm">
                    Built specifically for Pro WFM environments with deep understanding of workforce management needs.
                </flux:text>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="mt-16 w-full">
        <div class="rounded-lg bg-gradient-to-r p-8 text-center dark:from-teal-600 dark:to-emerald-700">
            <flux:heading level="2" class="mb-4 text-3xl! text-white">Ready to Get Started?</flux:heading>
            <flux:text class="text-lg text-white/90">
                Try our tools now or create an account to save your work and access advanced features
                <sup><b>*</b></sup>
            </flux:text>
            <div class="mt-4 flex flex-wrap justify-center gap-4">
                <flux:button variant="ghost" href="#tools">Try Tools Now</flux:button>
                <flux:button
                    variant="outline"
                    href="{{ route('register') }}"
                    class="border-white text-white hover:bg-white/10"
                >
                    Create Free Account
                </flux:button>
            </div>
        </div>

        <!-- Footnote -->
        <div class="mt-4 text-center">
            <flux:text class="text-[11px] text-zinc-500 dark:text-zinc-400">
                <sup><b>*</b></sup>
                Advanced features require registration. Registration restricted to ukg.com domain.
            </flux:text>
        </div>
    </section>
</x-layouts.guest>
