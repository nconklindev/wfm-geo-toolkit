<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        {{-- Dynamic Title --}}
        <title>{{ $title . ' | WFM Toolkit' ?? config('app.name', 'WFM Toolkit') }}</title>

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/coloris.js', 'resources/css/coloris.css', 'resources/js/app.js'])
        @fluxAppearance
        {{-- Add custom styles to the stack --}}
        @stack('styles')
    </head>
    <body class="min-h-screen flex-col bg-zinc-50 text-zinc-900 dark:bg-zinc-900 dark:text-zinc-200">
        <!-- Navigation -->
        <x-navbar-basic />

        <!-- Page Content -->
        <main class="container mx-auto max-w-11/12 flex-grow">
            {{ $slot }}
            {{-- Main content area for specific pages --}}
        </main>

        <!-- Footer -->
        @include('partials.footer')

        @fluxScripts
        @stack('scripts')
    </body>
</html>
