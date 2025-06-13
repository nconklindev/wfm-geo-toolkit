import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/map.js',
                'resources/js/echo.js',
                'resources/js/plotter.js',
                'resources/js/coloris.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    optimizeDeps: {
        include: ['leaflet'],
    },
    server: {
        host: 'localhost',
        watch: {
            usePolling: false,
        },
        cors: true,
        hmr: {
            host: 'localhost',
        },
    },
});
