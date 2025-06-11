<x-layouts.app :title="__('My Groups')">
    <div container class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Your Groups</flux:heading>
            <flux:modal.trigger name="create-group-modal">
                <flux:button icon="plus">Create Group</flux:button>
            </flux:modal.trigger>
        </div>

        @if ($groups->count() > 0)
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($groups as $group)
                    <div
                        class="cursor-pointer rounded-lg border border-zinc-200 bg-white p-6 transition-colors hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600"
                        onclick="window.location.href='{{ route('groups.show', $group) }}'"
                    >
                        <flux:heading size="lg" class="mb-3">{{ $group->name }}</flux:heading>

                        @if ($group->description)
                            <p class="mb-4 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ Str::limit($group->description, 100) }}
                            </p>
                        @endif

                        <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                            <span>
                                {{ $group->knownPlaces->count() }}
                                {{ Str::plural('place', $group->knownPlaces->count()) }}
                            </span>
                            <span>{{ $group->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div
                class="rounded-lg border border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900"
            >
                <flux:icon.user-group class="mx-auto mb-4 h-12 w-12 text-zinc-400 dark:text-zinc-500" />
                <flux:heading size="lg" class="mb-2">No groups yet</flux:heading>
                <p class="mb-6 text-zinc-600 dark:text-zinc-400">
                    Create your first group to organize your known places.
                </p>
                <flux:modal.trigger name="create-group-modal">
                    <flux:button icon="plus">Create Your First Group</flux:button>
                </flux:modal.trigger>
            </div>
        @endif

        <livewire:create-group-modal />
    </div>
</x-layouts.app>
