<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    {{-- From Vite's documentation, this HAS to be csp-nonce --}}
    <meta property="csp-nonce" content="{{ csp_nonce() }}" />

    {{-- Dynamic Title --}}
    <title>{{ $title . ' | WFM Toolkit' ?? config('app.name', 'WFM Toolkit') }}</title>

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Adding livewireStyles and passing the nonce fixes any issues with CSP --}}
    @fluxAppearance(['nonce' => csp_nonce()])
    @livewireStyles(['nonce' => csp_nonce()])

    {{-- Add custom styles to the stack --}}
    @stack('styles')
</head>
<body
    class="min-h-screen flex-col bg-linear-to-br from-zinc-50 via-zinc-100/80 to-zinc-100 text-zinc-900 dark:bg-zinc-900 dark:bg-linear-to-br dark:from-zinc-900 dark:via-zinc-800/20 dark:to-zinc-800/30 dark:text-zinc-200"
>
<!-- Navigation -->
<x-navbar-basic />

<!-- Page Content -->
<main class="container mx-auto max-w-11/12 flex-grow">
    {{ $slot }}
    {{-- Main content area for specific pages --}}
</main>

<!-- Footer -->
@include('partials.footer')

{{-- Adding livewireScripts and passing the nonce fixes any issues with CSP --}}
@fluxScripts(['nonce' => csp_nonce()])
@livewireScripts(['nonce' => csp_nonce()])

@stack('scripts')

<script @cspNonce>
    document.addEventListener('DOMContentLoaded', function () {
        // Dark mode toggle functionality
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        const html = document.documentElement;

        const savedTheme = localStorage.getItem('theme' || 'flux.appearance');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }

        darkModeToggle.addEventListener('click', function () {
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
                localStorage.setItem('flux.appearance', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
                localStorage.setItem('flux.appearance', 'dark');
            }
        });

        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
            if (!localStorage.getItem('theme' || 'flux.appearance')) {
                if (e.matches) {
                    html.classList.add('dark');
                } else {
                    html.classList.remove('dark');
                }
            }
        });

        // Close mobile menu on window resize (CSS-only approach doesn't need this but it's good UX)
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 768) {
                document.getElementById('mobile-menu-toggle').checked = false;
            }
        });
    });
</script>

</body>
</html>
