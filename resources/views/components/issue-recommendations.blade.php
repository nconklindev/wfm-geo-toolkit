@props([
    'type',
    'severityConfig',
])

<div class="{{ $severityConfig['bg-color-inner'] }} mt-2 rounded p-3">
    <flux:heading level="3" size="lg" class="{{ $severityConfig['text-color'] }} font-semibold">
        Recommended Actions:
    </flux:heading>
    
    @if ($type === 'outside_boundary')
        <ol class="{{ $severityConfig['text-color'] }} mt-1 list-inside list-disc space-y-1">
            <li>Consider expanding the geofence radius, if appropriate</li>
            <li>
                Ensure that the Function Access Profile control
                <b>Employee > Location Data > "Punch restrictions based on geofence area"</b>
                is set to
                <strong>Allowed</strong>
            </li>
            <ol class="mt-1 list-inside list-disc ps-5">
                <li>
                    This restricts employees from punching outside of Known Places which they are properly configured
                    for based on their location.
                </li>
            </ol>
            <li>
                (Optional) Set the Function Access Profile control
                <b>Employee > Location Data > "Punching outside of geofence area"</b>
                to
                <strong>Disallowed</strong>
            </li>
            <ol class="mt-1 list-inside list-disc ps-5">
                <li>
                    Setting this to "Disallowed" will prevent geofenced employees from punching outside of a Known Place
                </li>
            </ol>
            <li>Update employee punch policies if needed</li>
        </ol>
    @elseif ($type === 'accuracy_exceeded_max')
        <ol class="{{ $severityConfig['text-color'] }} mt-1 list-inside list-disc space-y-1">
            <li>Review the location settings with the employee on their mobile device</li>
            <x-mobile-location-instructions :text-color="$severityConfig['text-color']" />
            <li>
                <b>NOT RECOMMENDED:</b>
                Modify the value
                <b>site.timekeeping.MAX_ALLOWED_GEOLOCATION_ACCURACY</b>
                in
                <b>Application Setup > System Configuration > System Settings > Timekeeping</b>
            </li>
        </ol>
    @elseif ($type === 'low_accuracy')
        <ol class="{{ $severityConfig['text-color'] }} mt-1 list-inside list-disc space-y-1">
            <li>Review the location settings with the employee on their mobile device</li>
            <x-mobile-location-instructions :text-color="$severityConfig['text-color']" />
        </ol>
    @endif
</div>
