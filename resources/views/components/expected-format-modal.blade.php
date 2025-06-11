<flux:modal name="expected-format-modal" class="max-w-md md:min-w-xl">
    <div class="w-full overflow-auto rounded-lg bg-white dark:bg-zinc-800">
        <div class="mb-4 flex items-center justify-between">
            <flux:heading size="lg">Known Places JSON Format</flux:heading>
        </div>

        <pre class="overflow-x-scroll rounded-lg bg-zinc-900 p-4 font-mono text-sm">
<code class="language-json whitespace-pre-wrap font-mono text-wrap break-words">
[{
    "name": "Home Office",
    "latitude": 37.3318,
    "longitude": -122.0312,
    "radius": 100,
    "locations": [],
    "accuracy": 50,
    "is_active": true,
    "validationOrder": ["gps", "wifi"]
 },
 {
    "name": "Downtown Branch",
    "latitude": 37.7749,
    "longitude": -122.4194,
    "radius": 150,
    "locations": ['Acme/Acme Inc/North Carolina/Charlotte/Manufacturing'],
    "accuracy": 75,
    "is_active": true,
    "validationOrder": ["wifi", "gps"]
 }]
</code>
        </pre>
        <div class="mt-4">
            <flux:heading size="lg">Field Descriptions</flux:heading>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                <li>
                    <strong>name</strong>
                    : The name of the known place
                </li>
                <li>
                    <strong>latitude</strong>
                    : Latitude coordinate
                </li>
                <li>
                    <strong>longitude</strong>
                    : Longitude coordinate
                </li>
                <li>
                    <strong>radius</strong>
                    : Area radius in meters
                </li>
                <li>
                    <strong>locations</strong>
                    : Array of locations assigned
                </li>
                <li>
                    <strong>accuracy</strong>
                    : GPS accuracy threshold
                </li>
                <li>
                    <strong>is_active</strong>
                    : Whether the place is active
                </li>
                <li>
                    <strong>validationOrder</strong>
                    : Array of validation methods
                </li>
            </ul>
        </div>
        <flux:heading class="mt-8">Sample File Download</flux:heading>
        <flux:text>
            <flux:link href="{{ route('downloads.sample-known-places') }}">Download</flux:link>
        </flux:text>
    </div>
</flux:modal>
