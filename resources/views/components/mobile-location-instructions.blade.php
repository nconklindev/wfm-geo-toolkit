@props(['textColor' => 'text-zinc-600'])

<ol class="{{ $textColor }} mt-1 list-inside list-disc ps-5">
    <li>The most common cause of low accuracy is that the device is not allowing the application to access the GPS.</li>
    <p class="font-medium">On an Apple device:</p>
    <ol class="mt-1 list-inside list-disc ps-5">
        <li>
            Go to
            <b>Settings > Privacy & Security > Location Services</b>
        </li>
        <li>Locate the Pro app and ensure that Location Services are enabled</li>
    </ol>
    <p class="mt-2 font-medium">On an Android device:</p>
    <ol class="mt-1 list-inside list-disc ps-5">
        <li>Open Settings</li>
        <li>Tap "Location"</li>
        <li>
            Tap
            <b>App location permissions</b>
        </li>
        <li>Locate the Pro app and ensure that Location services are enabled</li>
    </ol>
</ol>
