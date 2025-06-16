<flux:footer class="mt-24 border-t border-zinc-200 py-12 dark:border-zinc-700">
    <div class="flex flex-col items-center justify-between md:flex-row">
        <div class="mb-6 md:mb-0">
            <div class="flex items-center sm:justify-center md:justify-start">
                <x-app-logo-icon class="size-4" />
                <span class="ml-2 block text-lg font-semibold">WFM Geo Toolkit</span>
            </div>
            <flux:text variant="subtle" size="sm" class="mt-2">
                © {{ date('Y') }} WFM Geo Toolkit. All rights reserved.
            </flux:text>
        </div>
        <div>
            <flux:navbar class="flex items-center">
                @auth
                    <flux:navbar.item href="{{ route('welcome') }}">Welcome</flux:navbar.item>
                @endauth

                <flux:navbar.item href="#">Support</flux:navbar.item>
                <flux:navbar.item href="#">Privacy</flux:navbar.item>
                <flux:navbar.item href="#">Terms</flux:navbar.item>
            </flux:navbar>
        </div>
    </div>
</flux:footer>
