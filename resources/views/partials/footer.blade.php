<flux:footer class="container mx-auto mt-24 border-t border-zinc-200 py-12 dark:border-zinc-700">
    <div class="flex flex-col items-center justify-between md:flex-row">
        <div class="mb-6 md:mb-0">
            <div class="flex items-center">
                <svg
                    class="h-8 w-8 text-accent"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                </svg>
                <span class="ml-2 text-lg font-semibold">WFM Geo Toolkit</span>
            </div>
            <p class="mt-2 text-sm text-muted">© {{ date('Y') }} WFM Geo Toolkit. All rights reserved.</p>
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
