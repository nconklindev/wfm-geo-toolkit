<x-layouts.app :title="__('Welcome to WFM Toolkit')">
    <div class="container mx-auto max-w-4xl px-4 py-12">
        <div class="text-center">
            <flux:icon.map-pin class="mx-auto mb-4 size-12 text-teal-500" />
            <flux:heading size="xl" level="1" class="mb-1 font-bold! text-shadow-lg/30!">
                Welcome to the WFM Toolkit!
            </flux:heading>
            <flux:text class="mb-10 text-base">
                Let's get you started with managing your geographic data effectively.
            </flux:text>
        </div>

        <div class="space-y-10">
            {{-- Section 1: Core Concepts --}}
            <div>
                <flux:heading
                    level="2"
                    size="xl"
                    class="mb-6 border-b border-zinc-200 pb-2 font-bold! dark:border-zinc-700"
                >
                    Understanding the Basics
                </flux:heading>
                <div class="flex flex-col">
                    {{-- Known Places Card --}}
                    <div
                        class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
                    >
                        <flux:icon name="map" class="mb-3 h-8 w-8 text-blue-500" />
                        <flux:heading level="3" size="xl" class="mb-2 font-semibold">
                            What are Known Places?
                        </flux:heading>
                        <flux:text>
                            <strong>Known Places</strong>
                            represent the physical locations of your business, such as:
                            <ul class="mt-2 list-inside list-disc">
                                <li>Offices</li>
                                <li>Retail Stores</li>
                                <li>Hospitals</li>
                            </ul>
                        </flux:text>
                        <flux:text class="mt-3">
                            You define them with a name, coordinates (latitude/longitude), a radius (size of the area),
                            and accuracy requirements.
                        </flux:text>
                        <flux:text class="mt-3">
                            They are used within Pro WFM to create geofenced areas around specific places in your
                            organization and determine where an employee is or is not allowed to punch from (given the
                            proper configuration within WFM).
                        </flux:text>
                    </div>
                </div>
            </div>

            {{-- Section 2: How to Get Started --}}
            <div>
                <flux:heading
                    level="2"
                    size="xl"
                    class="mb-6 border-b border-zinc-200 pb-2 text-2xl font-bold! dark:border-zinc-700"
                >
                    Getting Started: Your First Steps
                </flux:heading>
                <ol class="space-y-6">
                    <li class="flex items-start">
                        <div
                            class="mr-4 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-teal-700 text-white"
                        >
                            1
                        </div>
                        <div>
                            <flux:heading level="3" size="lg" class="font-semibold">Create Known Places</flux:heading>
                            <flux:text>
                                Head over to the
                                <flux:link href="{{ route('known-places.create') }}">Create</flux:link>
                                page to create a Known Place. Give your place a name, set its location on the map (or
                                enter coordinates), define its radius and accuracy, and associate it with the relevant
                                location(s) from Pro WFM. You can also organize your Known Places into groups for easier
                                management.
                            </flux:text>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <div
                            class="mr-4 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-teal-700 text-white"
                        >
                            2
                        </div>
                        <div>
                            <flux:heading level="3" size="lg" class="font-semibold">
                                Visit the Business Structure
                            </flux:heading>
                            <flux:text>
                                After your Known Places have been created and you've added some locations to them, visit
                                the
                                <flux:link href="{{ route('locations.index') }}">Locations</flux:link>
                                page to see your entire Business Structure created for you! Yes, it is
                                <span class="text-white">✨</span>
                                {{-- Escape Flux's text color change to show the emoji more clearly --}}
                                magic.
                                <span class="text-white">✨</span>
                            </flux:text>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <div
                            class="mr-4 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-teal-700 text-white"
                        >
                            3
                        </div>
                        <div>
                            <flux:heading level="3" size="lg" class="font-semibold">Explore the Dashboard</flux:heading>
                            <flux:text>
                                Your
                                <flux:link href="{{ route('dashboard') }}">Dashboard</flux:link>
                                provides a quick overview, including a map showing all your Known Places. It's a great
                                way to visualize your data.
                            </flux:text>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <div
                            class="mr-4 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-teal-700 text-white"
                        >
                            4
                        </div>
                        <div>
                            <flux:heading level="3" size="lg" class="font-semibold">
                                View Your Known Places
                            </flux:heading>
                            <flux:text>
                                You can view all of your existing Known Places on the
                                <flux:link href="{{ route('known-places.index') }}">index</flux:link>
                                page. Here, all of your places will display on the map. Clicking on a point will give a
                                brief description of which one you're looking at. Click on a row in the "Known Places"
                                table to instantly zoom to that place on the map. Zoom, zoom!
                            </flux:text>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <div
                            class="mr-4 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-teal-700 text-white"
                        >
                            5
                        </div>
                        <div>
                            <flux:heading level="3" size="lg" class="font-semibold">
                                Upload & Download Data
                            </flux:heading>
                            <flux:text>
                                We've tried to think of everything for you! What this means is that you don't have to
                                create every single Known Place that you have in WFM from scratch. You can
                                <flux:link href="{{ route('known-places.import') }}">upload</flux:link>
                                your data from WFM and we'll automatically create the Known Places for you.
                            </flux:text>
                            <flux:text class="mt-2">
                                Not only that, but we've also provided a way to
                                <flux:link>download</flux:link>
                                all of the Known Places that have been created in this tool. Anything created in here
                                can be exported in JSON or CSV format. Use JSON format for standard data visualization,
                                or use CSV with the "Transform" option for use with Pro WFM's Data Import Tool.
                            </flux:text>
                        </div>
                    </li>
                </ol>
            </div>
            {{-- Section 3: How to Get Started --}}
            <div>
                <flux:heading
                    level="2"
                    size="xl"
                    class="mb-6 border-b border-zinc-200 pb-2 text-2xl font-bold! dark:border-zinc-700"
                >
                    Feeling Lost?
                </flux:heading>
                <flux:text>
                    Don't worry, if you're ever stuck and not sure what to do or what anything does, you can come back
                    to this page by entering
                    <code>/welcome</code>
                    into your URL bar, or clicking the "Welcome" link at the bottom of the page.
                </flux:text>
            </div>

            {{-- Section 4: Call to Action --}}
            <div class="text-center">
                <flux:separator class="my-8" />
                <flux:heading level="2" size="xl" class="mb-4 font-semibold">Ready to Dive In?</flux:heading>
                <flux:text variant="subtle" size="lg" class="mb-6">
                    Explore the sections linked above or head straight to your dashboard.
                </flux:text>
                <flux:button :href="route('dashboard')" variant="primary">Go to Dashboard</flux:button>
            </div>
        </div>
    </div>
</x-layouts.app>
