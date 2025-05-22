<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-900">
        <!-- Sidebar -->
        @auth
            <flux:sidebar
                stashable
                sticky
                class="border-r border-zinc-200 bg-zinc-50 rtl:border-r-0 rtl:border-l dark:border-zinc-700 dark:bg-zinc-950"
            >
                <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
                <div class="flex flex-row items-center">
                    <x-app-logo />
                </div>

                <flux:navlist variant="outline">
                    <flux:navlist.item
                        icon="home"
                        href="{{ route('dashboard') }}"
                        :current="request()->routeIs('dashboard')"
                    >
                        {{ __('Dashboard') }}
                    </flux:navlist.item>

                    <flux:navlist.item
                        icon="building-office"
                        href="{{ route('locations.index') }}"
                        :current="request()->routeIs('locations.index')"
                    >
                        {{ __('Locations') }}
                    </flux:navlist.item>

                    {{-- Known Places --}}
                    <flux:navlist.group heading="Known Places" expandable>
                        <flux:navlist.item
                            href="{{ route('known-places.index') }}"
                            icon="eye"
                            :current="request()->routeIs('known-places.index')"
                        >
                            {{ __('View') }}
                        </flux:navlist.item>
                        <flux:navlist.item
                            href="{{ route('known-places.create') }}"
                            icon="plus"
                            :current="request()->routeIs('known-places.create')"
                        >
                            {{ __('Create') }}
                        </flux:navlist.item>
                        <flux:navlist.item
                            href="{{ route('known-places.wfm-import') }}"
                            icon="sparkles"
                            :current="request()->routeIs('known-places.wfm-import')"
                        >
                            {{ __('WFM Import') }}
                        </flux:navlist.item>
                        <flux:navlist.item
                            href="{{ route('known-places.import') }}"
                            icon="arrow-up-tray"
                            :current="request()->routeIs('known-places.import')"
                        >
                            {{ __('Upload') }}
                        </flux:navlist.item>
                        <flux:navlist.item
                            href="{{ route('known-places.export') }}"
                            icon="arrow-down-tray"
                            :current="request()->routeIs('known-places.export')"
                        >
                            {{ __('Download') }}
                        </flux:navlist.item>
                    </flux:navlist.group>

                    <flux:navlist.group heading="Known IP Addresses" icon="network" expandable>
                        <flux:navlist.item
                            icon="network"
                            href="{{ route('known-ip-addresses.index') }}"
                            :current="request()->routeIs('known-ip-addresses.index')"
                        >
                            Home
                        </flux:navlist.item>
                        <flux:navlist.item
                            icon="arrow-up-tray"
                            href="{{ route('known-ip-addresses.import') }}"
                            :current="request()->routeIs('known-ip-addresses.import')"
                        >
                            Import
                        </flux:navlist.item>
                    </flux:navlist.group>

                    {{-- TODO: Get the + icon somehow inline with the heading --}}
                    <flux:navlist.item class="pointer-events-none">
                        <div class="pointer-events-none flex items-center justify-between">
                            <span class="pointer-events-none">{{ __('Groups') }}</span>
                            <div class="items-center justify-between">
                                <flux:icon.plus class="pointer-events-auto size-4 cursor-pointer" />
                            </div>
                        </div>
                    </flux:navlist.item>
                </flux:navlist>
            </flux:sidebar>
        @endauth

        <flux:header class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950">
            <!-- Hamburger menu toggle -->
            <flux:sidebar.toggle class="lg:hidden" icon="bars-3" inset="left" />

            <!-- Tools -->
            <flux:dropdown>
                <flux:navbar.item icon="wrench" icon-trailing="chevron-down">Tools</flux:navbar.item>
                <flux:navmenu>
                    <flux:navmenu.item href="{{ route('tools.plotter') }}">Plotter</flux:navmenu.item>
                </flux:navmenu>
            </flux:dropdown>

            <flux:navbar.item icon="document-text">API Docs</flux:navbar.item>

            <flux:spacer />

            <!-- Search -->
            <flux:navbar class="mr-1.5 space-x-0.5 py-0!">
                <livewire:search />
            </flux:navbar>

            @auth
                {{-- Notification Center --}}
                <flux:tooltip content="Notification Center">
                    <flux:navbar.item
                        id="notification-badge"
                        x-data="notificationBadge"
                        href="{{ route('notifications') }}"
                        :badge="auth()->check() ? auth()->user()->unreadNotifications()->count() : 0"
                        badge-color="teal"
                        x-init="
                            currentCount =
                                {{ auth()->check() ? auth()->user()->unreadNotifications()->count() : 0 }}
                        "
                    >
                        <flux:icon.bell />
                    </flux:navbar.item>
                </flux:tooltip>
                <!-- Desktop User Menu -->
                <flux:dropdown position="top" align="end">
                    <flux:profile
                        class="cursor-pointer"
                        :initials="auth()->check() ? auth()->user()->initials() : '??'"
                    />

                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                        >
                                            {{ auth()->check() ? auth()->user()->initials() : '??' }}
                                        </span>
                                    </span>

                                    <div class="grid flex-1 text-left text-sm leading-tight">
                                        <span class="truncate font-semibold">
                                            {{ auth()->check() ? auth()->user()->username : 'Guest' }}
                                        </span>
                                        <span class="truncate text-xs">
                                            {{ auth()->check() ? auth()->user()->email : 'not logged in' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                                {{ __('Settings') }}
                            </flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item
                                as="button"
                                type="submit"
                                icon="arrow-right-start-on-rectangle"
                                class="w-full"
                            >
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            @else
                <!-- Guest user options -->
                <flux:dropdown position="top" align="end">
                    <flux:profile class="cursor-pointer" initials="?" />
                    <flux:menu>
                        <flux:menu.item :href="route('login')" icon="arrow-right-end-on-rectangle">
                            {{ __('Log In') }}
                        </flux:menu.item>
                        @if (Route::has('register'))
                            <flux:menu.item :href="route('register')" icon="user-plus">
                                {{ __('Register') }}
                            </flux:menu.item>
                        @endif
                    </flux:menu>
                </flux:dropdown>
            @endauth
        </flux:header>

        <!-- Mobile Menu -->
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
