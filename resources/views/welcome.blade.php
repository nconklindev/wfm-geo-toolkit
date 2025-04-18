<x-layouts.app :title="__('Welcome to WFM Geo Toolkit')">
    <div class="container mx-auto max-w-4xl px-4 py-12">
        <div class="text-center">
            <flux:icon.map-pin class="mx-auto mb-4 size-12 text-teal-500" />
            <flux:heading size="xl" level="1" class="mb-1 font-bold! text-shadow-lg/30!">
                Welcome to the WFM Geo Toolkit!
            </flux:heading>
            <flux:text class="mb-10 text-base">
                Let's get you started with managing your geographic data effectively.
            </flux:text>
        </div>

        <div class="space-y-10">
            {{-- Section 1: Core Concepts --}}
            <div>
                <flux:heading size="xl" class="mb-6 border-b border-zinc-200 pb-2 font-bold! dark:border-zinc-700">
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
                            are the core of the WFM Geo Toolkit. They represent the physical locations of your business,
                            such as:
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
                                page to create a Known Place . Give your place a name, set its location on the map (or
                                enter coordinates), define its radius and accuracy, and associate it with the relevant
                                location(s) from Pro WFM.
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
                                After your Known Places have been created and you've added some locations to them, you
                                can visit the
                                <flux:link href="{{ route('locations.index') }}">Locations</flux:link>
                                page to see how your data is organized. Don't worry about importing your Business
                                Structure from WFM. When you assign locations to Known Places, we'll automatically
                                create the hierarchy for you.
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
                            <flux:heading level="3" class="font-semibold">Explore the Dashboard</flux:heading>
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
                            <flux:heading level="3" class="font-semibold">Manage & Import/Export</flux:heading>
                            <flux:text>
                                You can edit or delete existing entries from the
                                <flux:link href="{{ route('known-places.index') }}">Known Places</flux:link>
                                list. Use the
                                <flux:link href="{{ route('known-places.import') }}">Import</flux:link>
                                /
                                <flux:link href="{{ route('known-places.export') }}">Export</flux:link>
                                features for bulk operations.
                            </flux:text>
                        </div>
                    </li>
                </ol>
            </div>

            {{-- Section 3: Call to Action --}}
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
