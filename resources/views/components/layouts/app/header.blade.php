<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-900">
        <flux:sidebar
            sticky
            stashable
            class="border-r border-zinc-200 bg-zinc-50 rtl:border-r-0 rtl:border-l dark:border-zinc-700 dark:bg-zinc-950"
        >
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
            <div id="logo-container" class="flex flex-row items-center">
                <x-app-logo-icon />
                <flux:brand :href="route('dashboard')" name="WFM Geo Toolkit" class="hidden px-2 dark:flex" />
            </div>
            <livewire:search variant="filled" placeholder="Search..." icon="magnifying-glass" tabindex="0" />
            <flux:navlist variant="outline">
                <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')">
                    Home
                </flux:navlist.item>
                <flux:navlist.item
                    id="notification-badge"
                    x-data="notificationBadge"
                    icon="bell"
                    :badge="auth()->check() ? auth()->user()->unreadNotifications()->count() : 0"
                    badge-color="teal"
                    :href="route('notifications')"
                    :current="request()->routeIs('notifications')"
                    x-init=""
                >
                    Notifications
                </flux:navlist.item>
                <flux:separator variant="subtle" class="my-1" />
                <flux:navlist.item icon="building-office" :href="route('locations.index')">Locations</flux:navlist.item>
                <flux:navlist.group expandable heading="Known Places" class="hidden lg:grid">
                    <flux:navlist.item :href="route('known-places.index')">View</flux:navlist.item>
                    <flux:navlist.item :href="route('known-places.create')">Create</flux:navlist.item>
                    <flux:navlist.item :href="route('known-places.wfm-import')">API Import</flux:navlist.item>
                    <flux:navlist.item :href="route('known-places.import')">Import</flux:navlist.item>
                    <flux:navlist.item :href="route('known-places.export')">Export</flux:navlist.item>
                </flux:navlist.group>
                <flux:navlist.group expandable expanded="false" heading="Known IP Addresses" class="hidden lg:grid">
                    <flux:navlist.item :href="route('known-ip-addresses.index')">View</flux:navlist.item>
                    <flux:navlist.item :href="route('known-ip-addresses.import')">Import</flux:navlist.item>
                </flux:navlist.group>
                <div class="mt-2 flex flex-col">
                    <div
                        class="relative my-px flex h-10 w-full items-center justify-between rounded-lg border border-transparent px-3 py-0 text-zinc-500 lg:h-8 dark:text-white/80"
                    >
                        <div class="flex-1 text-sm leading-none font-medium whitespace-nowrap">
                            <span>{{ __('Your groups') }}</span>
                        </div>
                        <flux:dropdown position="bottom" align="start">
                            <flux:button
                                size="xs"
                                variant="ghost"
                                type="button"
                                icon="ellipsis-horizontal"
                                class="cursor-pointer"
                            />
                            <flux:menu>
                                <flux:menu.item :href="route('groups.index')" icon="eye">
                                    View All Groups
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                        <flux:modal.trigger name="create-group-modal">
                            <flux:button size="xs" variant="ghost" type="button" icon="plus" class="cursor-pointer" />
                        </flux:modal.trigger>

                        <livewire:create-group-modal />
                    </div>
                    @auth
                        @php
                            $userGroups = auth()->user()->groups;
                            $maxGroups = 10;
                            $visibleGroups = $userGroups->take($maxGroups);
                            $hasMoreGroups = $userGroups->count() > $maxGroups;
                            $remainingCount = $userGroups->count() - $maxGroups;
                        @endphp

                        @foreach ($visibleGroups as $group)
                            <flux:navlist.item
                                class="mt-0! cursor-pointer gap-0! space-y-0!"
                                :href="route('groups.show', $group)"
                            >
                                <div class="mt-0 gap-0 space-y-0 font-normal">
                                    {{ $group->name }}
                                </div>
                            </flux:navlist.item>
                        @endforeach

                        @if ($hasMoreGroups)
                            <flux:navlist.item
                                icon="chevron-down"
                                class="cursor-pointer text-zinc-400 italic hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300"
                                onclick="alert('Show all groups functionality - you could redirect to a groups page or open a modal')"
                            >
                                View {{ $remainingCount }} more...
                            </flux:navlist.item>
                        @endif
                    @endauth
                </div>
            </flux:navlist>
            <flux:spacer />
            <flux:dropdown position="top" align="start" class="max-lg:hidden">
                <flux:profile
                    :initials="auth()->check() ? auth()->user()->initials() : '??'"
                    :name="auth()->user()->username"
                />
                <flux:menu>
                    <flux:menu.item icon="cog-6-tooth" :href="route('settings.profile')">Settings</flux:menu.item>
                    <flux:menu.item icon="information-circle" href="#">Help</flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item icon="arrow-right-start-on-rectangle" :href="route('logout')">
                        Logout
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>
        <flux:header
            class="block! border-b border-zinc-200 bg-white lg:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <flux:navbar class="w-full lg:hidden">
                <flux:sidebar.toggle class="lg:hidden" icon="bars-3" inset="left" />
                <flux:spacer />
                <flux:dropdown position="top" align="start" class="max-lg:hidden">
                    <flux:profile
                        :initials="auth()->check() ? auth()->user()->initials() : '??'"
                        :name="auth()->user()->username"
                    />
                    <flux:menu>
                        <flux:menu.item icon="arrow-right-start-on-rectangle" :href="route('logout')">
                            Logout
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </flux:navbar>
            <flux:navbar scrollable>
                <flux:navbar.item :href="route('dashboard')" :current="request()->routeIs('dashboard')">
                    Dashboard
                </flux:navbar.item>
                <flux:navbar.item class="cursor-pointer">API Docs (Coming soon!)</flux:navbar.item>
                <flux:dropdown class="hidden md:flex">
                    <flux:navbar.item icon-trailing="chevron-down" class="cursor-pointer">Tools</flux:navbar.item>
                    <flux:navmenu>
                        <flux:navmenu.item
                            :href="route('tools.plotter')"
                            :current="request()->routeIs('tools.plotter')"
                        >
                            Plotter
                        </flux:navmenu.item>
                    </flux:navmenu>
                </flux:dropdown>
                <flux:spacer />
                <flux:navbar.item icon="github" external href="https://github.com/nconklindev/wfm-geo-toolkit" />
            </flux:navbar>
        </flux:header>
        {{ $slot }}
        @fluxScripts
    </body>
    @auth
        <script>
            document.addEventListener('alpine:init', () => {
                console.log('Alpine initialized...');
                Alpine.data('notificationBadge', () => ({
                    currentCount: 0,

                    init() {
                        this.currentCount = {{ auth()->user()->unreadNotifications()->count() }};
                        console.log('Current notification count is: ', this.currentCount);

                        // Use the websocket
                        Echo.private('App.Models.User.{{ auth()->user()->id }}').notification((notification) => {
                            console.log('Notification received!', notification);

                            // Use the count from the notification if it exists
                            if (notification.count !== undefined) {
                                this.currentCount = notification.count;
                                console.log('Notification included count:', this.currentCount);
                            } else {
                                // Fallback: increment the current count
                                this.currentCount++;
                                console.log('Incremented count to:', this.currentCount);
                            }

                            this.updateNotificationBadge();
                        });
                    },

                    updateNotificationBadge() {
                        console.log('Updating notification badge to:', this.currentCount);

                        // Get the badge element
                        const badgeElement = document.getElementById('notification-badge');
                        if (!badgeElement) return;

                        // Set the badge attribute
                        badgeElement.setAttribute('badge', this.currentCount);

                        // Check if the badge span exists
                        let badgeSpan = badgeElement.querySelector('span.text-xs.font-medium.rounded-sm');

                        // If the badge span doesn't exist but should (count > 0), create it
                        if (!badgeSpan && this.currentCount > 0) {
                            badgeSpan = document.createElement('span');
                            badgeSpan.className =
                                'text-xs font-medium rounded-sm px-1 py-0.5 text-teal-800 dark:text-teal-200 bg-teal-400/20 dark:bg-teal-400/40 ms-2';
                            badgeElement.appendChild(badgeSpan);
                        }

                        // Update the badge text if it exists
                        if (badgeSpan) {
                            if (this.currentCount > 0) {
                                badgeSpan.textContent = this.currentCount;
                                badgeSpan.style.display = ''; // Make sure it's visible
                            } else {
                                // Hide the badge if count is 0
                                badgeSpan.style.display = 'none';
                            }
                        }

                        // Add a simple flash animation
                        badgeElement.classList.add('notification-pulse');
                        setTimeout(() => {
                            badgeElement.classList.remove('notification-pulse');
                        }, 1000);
                    },
                }));
            });
        </script>

        <style>
            @keyframes notification-pulse {
                0% {
                    transform: scale(1);
                }
                50% {
                    transform: scale(1.1);
                }
                100% {
                    transform: scale(1);
                }
            }

            .notification-pulse {
                animation: notification-pulse 0.5s ease-in-out;
            }
        </style>
    @endauth
</html>
