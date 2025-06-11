<flux:modal name="ip-format-modal" {{ $attributes->class(['md:w-2xl']) }}>
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Expected JSON Format</flux:heading>
            <flux:text class="mt-2">Your JSON file should follow this structure:</flux:text>
        </div>

        <div class="rounded-lg bg-zinc-100 p-4 dark:bg-zinc-700">
            <pre class="text-sm text-zinc-800 dark:text-zinc-200"><code>[
  {
    "sdmKey": "3fa85f64-5717-4562-b3fc-2c963f66afa6",
    "description": "string",
    "endingIPRange": "string",
    "startingIPRange": "string",
    "protocolVersion": {
      "id": 0,
      "qualifier": "string"
    },
    "id": 0,
    "name": "string"
  }
]</code></pre>
        </div>

        <div class="space-y-2">
            <flux:text size="sm" class="font-medium">Required fields:</flux:text>
            <ul class="space-y-1 text-sm text-zinc-600 dark:text-zinc-400">
                <li>
                    •
                    <strong>name</strong>
                    : Unique name for the IP range
                </li>
                <li>
                    •
                    <strong>start</strong>
                    : Starting IP address (IPv4)
                </li>
                <li>
                    •
                    <strong>end</strong>
                    : Ending IP address (IPv4)
                </li>
            </ul>
            <flux:text size="sm" class="mt-3 font-medium">Optional fields:</flux:text>
            <ul class="space-y-1 text-sm text-zinc-600 dark:text-zinc-400">
                <li>
                    •
                    <strong>description</strong>
                    : Additional details about the IP range
                </li>
            </ul>
        </div>

        <div class="flex justify-end">
            <flux:modal.close>
                <flux:button variant="primary">Got it</flux:button>
            </flux:modal.close>
        </div>
    </div>
</flux:modal>
