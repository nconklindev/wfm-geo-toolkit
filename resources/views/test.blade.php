<x-layouts.app title="Test Dashboard">
    {{-- Use your app's layout --}}

    {{-- Include Leaflet CSS --}}
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    />

    <style>
        /* Set a height for the map container */
        #map {
            height: 400px;
        }
    </style>

    <div class="container mx-auto p-4">
        <h1 class="mb-4 text-2xl font-bold">Dashboard Charts</h1>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            {{-- Map Card --}}
            <div class="rounded-lg border bg-white p-4 shadow dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="mb-3 text-xl font-semibold">Known Places Map</h2>
                <div id="map" class="z-0"></div>
                {{-- Map container --}}
            </div>

            {{-- Coverage Chart Card --}}
            <div class="rounded-lg border bg-white p-4 shadow dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="mb-3 text-xl font-semibold">Leaf Node Coverage by Known Places</h2>
                <div class="mx-auto max-w-xs">
                    {{-- Add data attribute and REMOVE inline JS for this chart --}}
                    <canvas id="coverageChart" data-coverage="{{ json_encode($coverageData) }}"></canvas>
                </div>
            </div>

            {{-- Add more cards for other charts here --}}
        </div>
    </div>

    {{-- Include Leaflet JS --}}
    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>

    {{-- Remove the large inline <script> block that contained BOTH Leaflet and Chart init --}}
    {{-- Add a @push block for page-specific scripts --}}
    <!-- Chart.js -->
    @vite('resources/js/coverage.js')
    @push('scripts')
        {{-- Keep the Leaflet initialization script here --}}
        <script>
            // Ensure this runs after Leaflet library is loaded
            document.addEventListener('DOMContentLoaded', function () {
                // --- Leaflet Map Initialization ---
                const placesData = @json($placesForMap); // Get data from PHP

                // Find map element AFTER DOM is loaded
                const mapElement = document.getElementById('map');
                if (mapElement) {
                    // Check if map element exists on this page
                    // Initialize map centered roughly (adjust coordinates as needed)
                    const map = L.map(mapElement).setView(
                        [placesData[0]?.lat || 51.505, placesData[0]?.lng || -0.09],
                        10,
                    ); // Use first place or default

                    // Add OpenStreetMap tile layer
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap contributors',
                    }).addTo(map);

                    // Add markers and circles for each place
                    placesData.forEach((place) => {
                        if (place.lat && place.lng) {
                            // Add a marker
                            L.marker([place.lat, place.lng])
                                .addTo(map)
                                .bindPopup(`<b>${place.name}</b><br>Radius: ${place.radius}m`);

                            // Add a circle representing the radius
                            L.circle([place.lat, place.lng], {
                                color: 'blue',
                                fillColor: '#30f',
                                fillOpacity: 0.2,
                                radius: place.radius, // Radius in meters
                            }).addTo(map);
                        }
                    });

                    // Optional: Adjust map bounds to fit all markers if needed
                    if (placesData.length > 0) {
                        const bounds = L.latLngBounds(placesData.map((p) => [p.lat, p.lng]));
                        // Add padding to bounds because circles extend beyond marker center
                        map.fitBounds(bounds.pad(0.3)); // Adjust padding as needed
                    }
                }
            });
            // --- Add initialization for other charts here (if inline) ---
        </script>
    @endpush
</x-layouts.app>
