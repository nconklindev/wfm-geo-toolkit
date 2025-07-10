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
                    Your comprehensive solution for geofencing, API exploration, network analysis, and more. Everything
                    you need in one powerful platform.
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
        <div class="rounded-lg bg-gradient-to-r from-teal-600 to-emerald-700 p-8 text-center">
            <flux:heading level="2" class="mb-4 text-3xl! text-white">Ready to Get Started?</flux:heading>
            <flux:text class="text-lg text-white/90">
                Try our tools now or create an account to save your work and access advanced features
                <sup><b>*</b></sup>
            </flux:text>
            <div class="mt-4 flex flex-wrap justify-center gap-4">
                <flux:button variant="ghost" href="#tools">Try Tools Now</flux:button>
                <flux:button href="{{ route('register') }}" class="border-white text-white hover:bg-white/10">
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

    <!-- Powered By Section -->
    <section class="mt-16 w-full">
        <div
            class="rounded-lg border border-zinc-200 bg-gradient-to-r from-zinc-50 to-zinc-100 p-8 dark:border-zinc-700 dark:from-zinc-800 dark:to-zinc-900"
        >
            <div class="text-center">
                <flux:heading level="3" class="mb-6 text-lg font-semibold text-zinc-700 dark:text-zinc-300">
                    Powered By
                </flux:heading>
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6">
                    <!-- Laravel -->
                    <div class="flex flex-col items-center space-y-2">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg text-[#FF2D20]">
                            <svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <title>Laravel</title>
                                <path
                                    fill="currentColor"
                                    d="M23.642 5.43a.364.364 0 01.014.1v5.149c0 .135-.073.26-.189.326l-4.323 2.49v4.934a.378.378 0 01-.188.326L9.93 23.949a.316.316 0 01-.066.027c-.008.002-.016.008-.024.01a.348.348 0 01-.192 0c-.011-.002-.02-.008-.03-.012-.02-.008-.042-.014-.062-.025L.533 18.755a.376.376 0 01-.189-.326V2.974c0-.033.005-.066.014-.098.003-.012.01-.02.014-.032a.369.369 0 01.023-.058c.004-.013.015-.022.023-.033l.033-.045c.012-.01.025-.018.037-.027.014-.012.027-.024.041-.034H.53L5.043.05a.375.375 0 01.375 0L9.93 2.647h.002c.015.01.027.021.04.033l.038.027c.013.014.02.03.033.045.008.011.02.021.025.033.01.02.017.038.024.058.003.011.01.021.013.032.01.031.014.064.014.098v9.652l3.76-2.164V5.527c0-.033.004-.066.013-.098.003-.01.01-.02.013-.032a.487.487 0 01.024-.059c.007-.012.018-.02.025-.033.012-.015.021-.03.033-.043.012-.012.025-.02.037-.028.014-.01.026-.023.041-.032h.001l4.513-2.598a.375.375 0 01.375 0l4.513 2.598c.016.01.027.021.042.031.012.01.025.018.036.028.013.014.022.03.034.044.008.012.019.021.024.033.011.02.018.04.024.06.006.01.012.021.015.032zm-.74 5.032V6.179l-1.578.908-2.182 1.256v4.283zm-4.51 7.75v-4.287l-2.147 1.225-6.126 3.498v4.325zM1.093 3.624v14.588l8.273 4.761v-4.325l-4.322-2.445-.002-.003H5.04c-.014-.01-.025-.021-.04-.031-.011-.01-.024-.018-.035-.027l-.001-.002c-.013-.012-.021-.025-.031-.04-.01-.011-.021-.022-.028-.036h-.002c-.008-.014-.013-.031-.02-.047-.006-.016-.014-.027-.018-.043a.49.49 0 01-.008-.057c-.002-.014-.006-.027-.006-.041V5.789l-2.18-1.257zM5.23.81L1.47 2.974l3.76 2.164 3.758-2.164zm1.956 13.505l2.182-1.256V3.624l-1.58.91-2.182 1.255v9.435zm11.581-10.95l-3.76 2.163 3.76 2.163 3.759-2.164zm-.376 4.978L16.21 7.087 14.63 6.18v4.283l2.182 1.256 1.58.908zm-8.65 9.654l5.514-3.148 2.756-1.572-3.757-2.163-4.323 2.489-3.941 2.27z"
                                />
                            </svg>
                        </div>
                        <flux:text size="xs" class="font-medium">Laravel</flux:text>
                    </div>

                    <!-- PHP -->
                    <div class="flex flex-col items-center space-y-2">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-[#777BB4]">
                            <svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <title>PHP</title>
                                <path
                                    fill="currentColor"
                                    d="M7.01 10.207h-.944l-.515 2.648h.838c.556 0 .97-.105 1.242-.314.272-.21.455-.559.55-1.049.092-.47.05-.802-.124-.995-.175-.193-.523-.29-1.047-.29zM12 5.688C5.373 5.688 0 8.514 0 12s5.373 6.313 12 6.313S24 15.486 24 12c0-3.486-5.373-6.312-12-6.312zm-3.26 7.451c-.261.25-.575.438-.917.551-.336.108-.765.164-1.285.164H5.357l-.327 1.681H3.652l1.23-6.326h2.65c.797 0 1.378.209 1.744.628.366.418.476 1.002.33 1.752a2.836 2.836 0 0 1-.305.847c-.143.255-.33.49-.561.703zm4.024.715l.543-2.799c.063-.318.039-.536-.068-.651-.107-.116-.336-.174-.687-.174H11.46l-.704 3.625H9.388l1.23-6.327h1.367l-.327 1.682h1.218c.767 0 1.295.134 1.586.401s.378.7.263 1.299l-.572 2.944h-1.389zm7.597-2.265a2.782 2.782 0 0 1-.305.847c-.143.255-.33.49-.561.703a2.44 2.44 0 0 1-.917.551c-.336.108-.765.164-1.286.164h-1.18l-.327 1.682h-1.378l1.23-6.326h2.649c.797 0 1.378.209 1.744.628.366.417.477 1.001.331 1.751zM17.766 10.207h-.943l-.516 2.648h.838c.557 0 .971-.105 1.242-.314.272-.21.455-.559.551-1.049.092-.47.049-.802-.125-.995s-.524-.29-1.047-.29z"
                                />
                            </svg>
                        </div>
                        <flux:text size="xs" class="font-medium">PHP</flux:text>
                    </div>

                    <!-- TailwindCSS -->
                    <div class="flex flex-col items-center space-y-2">
                        <div class="flex h-12 w-12 items-center justify-center text-[#06B6D4]">
                            <svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <title>Tailwind CSS</title>
                                <path
                                    fill="currentColor"
                                    d="M12.001,4.8c-3.2,0-5.2,1.6-6,4.8c1.2-1.6,2.6-2.2,4.2-1.8c0.913,0.228,1.565,0.89,2.288,1.624 C13.666,10.618,15.027,12,18.001,12c3.2,0,5.2-1.6,6-4.8c-1.2,1.6-2.6,2.2-4.2,1.8c-0.913-0.228-1.565-0.89-2.288-1.624 C16.337,6.182,14.976,4.8,12.001,4.8z M6.001,12c-3.2,0-5.2,1.6-6,4.8c1.2-1.6,2.6-2.2,4.2-1.8c0.913,0.228,1.565,0.89,2.288,1.624 c1.177,1.194,2.538,2.576,5.512,2.576c3.2,0,5.2-1.6,6-4.8c-1.2,1.6-2.6,2.2-4.2,1.8c-0.913-0.228-1.565-0.89-2.288-1.624 C10.337,13.382,8.976,12,6.001,12z"
                                />
                            </svg>
                        </div>
                        <flux:text size="xs" class="font-medium">Tailwind</flux:text>
                    </div>

                    <!-- Alpine.js -->
                    <div class="flex flex-col items-center space-y-2">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg text-[#8BC0D0]">
                            <svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <title>Alpine.js</title>
                                <path
                                    fill="currentColor"
                                    d="m24 12-5.72 5.746-5.724-5.741 5.724-5.75L24 12zM5.72 6.254 0 12l5.72 5.746h11.44L5.72 6.254z"
                                />
                            </svg>
                        </div>
                        <flux:text size="xs" class="font-medium">Alpine.js</flux:text>
                    </div>

                    <!-- Livewire -->
                    <div class="flex flex-col items-center space-y-2">
                        <div class="flex h-12 w-12 items-center justify-center text-pink-500">
                            <svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <title>Livewire</title>
                                <path
                                    fill="currentColor"
                                    d="M12.001 0C6.1735 0 1.4482 4.9569 1.4482 11.0723c0 2.0888.5518 4.0417 1.5098 5.709.2492.2796.544.4843.9649.4843 1.3388 0 1.2678-2.0644 2.6074-2.0644 1.3395 0 1.4111 2.0644 2.75 2.0644 1.3388 0 1.2659-2.0644 2.6054-2.0644.5845 0 .9278.3967 1.2403.8398-.2213-.2055-.4794-.3476-.8203-.3476-1.1956 0-1.3063 1.6771-2.2012 2.1406v4.5097c0 .9145.7418 1.6563 1.6562 1.6563.9145 0 1.6563-.7418 1.6563-1.6563v-5.8925c.308.4332.647.8144 1.2207.8144 1.3388 0 1.266-2.0644 2.6055-2.0644.465 0 .7734.2552 1.039.58-.1294-.0533-.2695-.0878-.4297-.0878-1.1582 0-1.296 1.574-2.1171 2.0937v2.4356c0 .823.6672 1.4902 1.4902 1.4902s1.4902-.6672 1.4902-1.4902V16.371c.3234.4657.6684.8945 1.2774.8945.7955 0 1.093-.7287 1.4843-1.3203.6878-1.4704 1.0743-3.1245 1.0743-4.873C22.5518 4.9569 17.8284 0 12.001 0zm-.5664 2.877c2.8797 0 5.2148 2.7836 5.2148 5.8066 0 3.023-1.5455 5.1504-5.2148 5.1504-3.6693 0-5.2149-2.1274-5.2149-5.1504S8.5548 2.877 11.4346 2.877zM10.0322 4.537a1.9554 2.1583 0 00-1.955 2.1582 1.9554 2.1583 0 001.955 2.1582 1.9554 2.1583 0 001.9551-2.1582 1.9554 2.1583 0 00-1.955-2.1582zm-.3261.664a.9777.9961 0 01.9785.9962.9777.9961 0 01-.9785.996.9777.9961 0 01-.9766-.996.9777.9961 0 01.9766-.9961zM6.7568 15.6935c-1.0746 0-1.2724 1.3542-1.9511 1.9648v1.7813c0 .823.6672 1.4902 1.4902 1.4902s1.4902-.6672 1.4902-1.4902v-3.1817c-.2643-.3237-.5767-.5644-1.0293-.5644Z"
                                />
                            </svg>
                        </div>
                        <flux:text size="xs" class="font-medium">Livewire</flux:text>
                    </div>

                    <!-- Leaflet -->
                    <div class="flex flex-col items-center space-y-2">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg text-[#199900]">
                            <svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <title>Leaflet</title>
                                <path
                                    fill="currentColor"
                                    d="M17.69 0c-.355.574-8.432 4.74-10.856 8.649-2.424 3.91-3.116 6.988-2.237 9.882.879 2.893 2.559 2.763 3.516 3.717.958.954 2.257 2.113 4.332 1.645 2.717-.613 5.335-2.426 6.638-7.508 1.302-5.082.448-9.533-.103-11.99A35.395 35.395 0 0 0 17.69 0zm-.138.858l-9.22 21.585-.574-.577Z"
                                />
                            </svg>
                        </div>
                        <flux:text size="xs" class="font-medium">Leaflet</flux:text>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.guest>
