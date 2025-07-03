import 'leaflet/dist/leaflet.css';
import './coloris.js';
import './clipboard.js';

import.meta.glob(['../fonts/**']);

document.addEventListener('DOMContentLoaded', function () {
    // Basic configuration
    Coloris({
        el: '[data-color]',
        inline: false,
        defaultColor: '#29a4d3',
        margin: 8,
        wrap: true,
        theme: 'default', // Available themes: default, large, polaroid
        themeMode: 'auto', // light or dark
        format: 'hex', // hex, rgb, hsl
        alpha: true, // Enable alpha channel
        swatches: [
            '#FF5733', // Vibrant Orange
            '#33FF57', // Bright Green
            '#3357FF', // Bold Blue
            '#F1C40F', // Golden Yellow
            '#9B59B6', // Soft Purple
            '#E67E22', // Warm Brownish Orange
            '#1ABC9C', // Aqua Cyan
            '#34495E', // Neutral Dark Gray
        ],
    });
});
