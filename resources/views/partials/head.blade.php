<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
@auth
    <meta name="user-id" content="{{ auth()->id() }}" />
@endauth

<title>{{ $title . ' | WFM Geo Toolkit' ?? 'WFM Geo Toolkit' }}</title>

{{-- Stack for page-specific scripts --}}
@stack('scripts')

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
