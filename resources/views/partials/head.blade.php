<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
@auth
    <meta name="user-id" content="{{ auth()->id() }}" />
@endauth

<title>{{ $title . ' | WFM Geo Toolkit' ?? 'WFM Geo Toolkit' }}</title>

<link rel="preconnect" href="https://fonts.bunny.net" crossorigin />
<link rel="preload" href="https://fonts.bunny.net/css?family=inter:100,200,300,400,500,600,700,800,900" />
<link rel="stylesheet" href="https://fonts.bunny.net/css?family=inter:100,200,300,400,500,600,700,800,900" />

{{-- Stack for page-specific scripts --}}
@stack('scripts')

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
