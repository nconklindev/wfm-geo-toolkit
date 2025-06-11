<x-layouts.app.header :title="$title ?? null" :unread-notifications-count="$unreadNotificationsCount ?? 0">
    <flux:main>
        {{ $slot }}
    </flux:main>
    @include('partials.footer')
</x-layouts.app.header>
