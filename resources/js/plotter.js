import L from 'leaflet';
// Explicitly import the icons from Leaflet so Vite knows what to serve
// Taken from https://cescobaz.com/2023/06/14/setup-leaflet-with-svelte-and-vite/
import markerIconUrl from 'leaflet/dist/images/marker-icon.png';
import markerShadowUrl from 'leaflet/dist/images/marker-shadow.png';
import markerIconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png';

document.addEventListener('DOMContentLoaded', () => {
    console.log('[Plotter] DOM fully loaded and parsed. Attempting Plotter map setup.');

    const mapElement = document.getElementById('map');
    if (!mapElement || mapElement.dataset.mapType !== 'plotter') {
        // If it's not the plotter map, or the element doesn't exist, bail out
        console.log('[Plotter] Map element not found or not for plotter. Aborting plotter script.');
        return;
    }

    // Check if Leaflet is loaded
    if (typeof L === 'undefined') {
        console.error('[Plotter] Leaflet (L) is UNDEFINED! Check Vite build (resources/js/app.js).');
        return;
    }
    console.log('[Plotter] Leaflet (L) is defined. Proceeding with Plotter map initialization.');

    let plotterMap = null;
    let plottedLayers = {}; // Store layers for plotted points { index: layerGroup }
    let previewMarker = null;
    let previewCircle = null;
    let mapInitialized = false;
    let isDragging = false;

    // --- Input Elements ---
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const radiusInput = document.getElementById('radius');
    const colorInput = document.getElementById('color');
    const labelInput = document.getElementById('label');

    // --- Default Values ---
    const defaultLat = 42.6146421515;
    const defaultLng = -71.324701513;
    const defaultRadius = 75;
    const defaultColor = '#3b82f6'; // Match Livewire component default
    const defaultZoom = 15;

    function initializeMap() {
        if (mapInitialized) {
            console.log('[Plotter] Map already initialized. Skipping.');
            // Ensure the map size is correct if re-initializing logic is ever added
            if (plotterMap) {
                try {
                    plotterMap.invalidateSize();
                } catch (e) {
                    /* ignore */
                }
            }
            return;
        }
        if (mapElement._leaflet_id) {
            console.warn('[Plotter] Map element already has Leaflet ID. Potential double init. Attempting removal.');
            try {
                if (plotterMap) plotterMap.remove();
            } catch (e) {
                console.error('[Plotter] Error removing previous map instance:', e);
            }
            delete mapElement._leaflet_id;
            plotterMap = null;
        }

        console.log('[Plotter] Initializing Plotter Leaflet map.');
        try {
            plotterMap = L.map('map', {
                zoomControl: false, // Add zoom control manually
                maxBounds: [
                    [-90, -180],
                    [90, 180],
                ], // Prevent showing multiple worlds
                maxBoundsViscosity: 1.0, // Make the bounds "sticky" - prevent panning outside
                minZoom: 2.0,
            });
            mapInitialized = true; // Set flag after successful init
            console.log('[Plotter] L.map created.');

            L.control.zoom({ position: 'bottomleft' }).addTo(plotterMap);
            const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            });

            const googleSatellite = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                attribution: '&copy; <a href="https://www.google.com/maps">Google</a>',
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            });

            // Add default layer (OSM)
            osmLayer.addTo(plotterMap);

            // Create layer control
            const baseLayers = {
                OpenStreetMap: osmLayer,
                'Google Satellite': googleSatellite,
            };

            // Add the controls
            L.control
                .layers(baseLayers, null, {
                    position: 'bottomright',
                    collapsed: false,
                })
                .addTo(plotterMap);

            // Set the image URLs for Leaflet
            L.Icon.Default.prototype.options.iconUrl = markerIconUrl;
            L.Icon.Default.prototype.options.shadowUrl = markerShadowUrl;
            L.Icon.Default.prototype.options.iconRetinaUrl = markerIconRetinaUrl;
            L.Icon.Default.imagePath = '';

            // Set the initial view
            plotterMap.setView([defaultLat, defaultLng], defaultZoom);

            // Initialize with points passed from Livewire/Blade
            const initialPointsJson = mapElement.dataset.mapPoints;
            if (initialPointsJson) {
                try {
                    const initialPoints = JSON.parse(initialPointsJson);
                    console.log('[Plotter] Initial points for map: ', initialPoints);
                    updateMapPoints(initialPoints); // Draw initial points
                } catch (e) {
                    console.error('[Plotter] Error parsing initial map points:', e);
                }
            } else {
                console.log('[Plotter] No initial points found.');
            }

            // Initialize preview marker/circle
            initializePreviewElements();

            // Set up Livewire/Browser event listeners
            setupEventListeners();
        } catch (e) {
            console.error('[Plotter] CRITICAL: Failed to initialize Leaflet map:', e);
            mapInitialized = false; // Reset flag on failure
        }
    }

    function initializePreviewElements() {
        if (!plotterMap) return;

        const initialLat = parseFloat(latitudeInput?.value) || defaultLat;
        const initialLng = parseFloat(longitudeInput?.value) || defaultLng;
        const initialRadius = parseInt(radiusInput?.value) || defaultRadius;
        const initialColor = colorInput?.value || defaultColor;

        // Remove existing preview elements if they exist
        if (previewMarker) previewMarker.remove();
        if (previewCircle) previewCircle.remove();

        console.log('[Plotter] Initializing preview elements at:', [initialLat, initialLng]);

        previewMarker = L.marker([initialLat, initialLng], {
            draggable: true,
            opacity: 0.65, // Make preview slightly transparent
            zIndexOffset: 1000, // Ensure the preview marker is on top
        }).addTo(plotterMap);

        previewCircle = L.circle([initialLat, initialLng], {
            radius: initialRadius,
            color: initialColor,
            fillColor: initialColor,
            fillOpacity: 0.2, // Make preview slightly transparent
            weight: 1,
            dashArray: '5, 5', // Dashed line for preview circle
        }).addTo(plotterMap);

        // Add drag listeners to track dragging state
        previewMarker.on('dragstart', function () {
            isDragging = true;
        });

        previewMarker.on('dragend', function () {
            setTimeout(() => {
                isDragging = false;
            }, 50); // Small delay to prevent click events immediately after drag

            const position = previewMarker.getLatLng();
            console.log('[Plotter] Preview marker dragged to:', position);
            if (latitudeInput) latitudeInput.value = position.lat.toFixed(10); // Use more precision
            if (longitudeInput) longitudeInput.value = position.lng.toFixed(10);

            // IMPORTANT: Dispatch 'input' event so Livewire updates
            if (latitudeInput) latitudeInput.dispatchEvent(new Event('input', { bubbles: true }));
            if (longitudeInput) longitudeInput.dispatchEvent(new Event('input', { bubbles: true }));

            // Update the circle position immediately
            if (previewCircle) previewCircle.setLatLng(position);
        });

        // Add click functionality to place marker
        plotterMap.on('click', function (e) {
            // Don't process click if we're in the middle of dragging
            if (isDragging) {
                console.log('[Plotter] Ignoring click during drag operation');
                return;
            }

            const clickedLatLng = e.latlng;
            console.log('[Plotter] Map clicked at:', clickedLatLng);

            // Update preview marker position
            if (previewMarker) {
                previewMarker.setLatLng(clickedLatLng);
            }

            // Update preview circle position
            if (previewCircle) {
                previewCircle.setLatLng(clickedLatLng);
            }

            // Update form inputs
            if (latitudeInput) latitudeInput.value = clickedLatLng.lat.toFixed(10);
            if (longitudeInput) longitudeInput.value = clickedLatLng.lng.toFixed(10);

            // Dispatch input events to trigger Livewire updates
            if (latitudeInput) latitudeInput.dispatchEvent(new Event('input', { bubbles: true }));
            if (longitudeInput) longitudeInput.dispatchEvent(new Event('input', { bubbles: true }));
        });

        // Initial map centering on preview
        updatePreviewElements(false); // Call once to sync visuals if needed
    }

    function updatePreviewElements(setMapView = false) {
        if (!plotterMap || !previewMarker || !previewCircle) {
            console.warn('[Plotter] Cannot update preview elements: Map or elements not ready.');
            return;
        }

        const lat = parseFloat(latitudeInput?.value) || defaultLat;
        const lng = parseFloat(longitudeInput?.value) || defaultLng;
        const radius = parseInt(radiusInput?.value) || defaultRadius;
        const color =
            colorInput?.value && /^#([0-9A-F]{3}){1,2}$/i.test(colorInput.value) ? colorInput.value : defaultColor; // Fallback to default if invalid

        const latLng = [lat, lng];

        // Update Marker
        previewMarker.setLatLng(latLng);

        // Update Circle
        previewCircle.setLatLng(latLng);
        previewCircle.setRadius(radius);
        previewCircle.setStyle({ color: color, fillColor: color });

        // Optionally update map view
        if (setMapView) {
            plotterMap.panTo(latLng); // Use panTo for a smoother transition
        }
    }

    function setupEventListeners() {
        if (!mapInitialized) return;
        console.log('[Plotter] Setting up event listeners.');

        // --- Listen to Input Changes for Preview ---
        latitudeInput?.addEventListener('input', () => updatePreviewElements(true)); // Pan map on lat change
        longitudeInput?.addEventListener('input', () => updatePreviewElements(true)); // Pan map on lng change
        radiusInput?.addEventListener('input', () => updatePreviewElements(false)); // Don't pan on radius change
        colorInput?.addEventListener('input', () => updatePreviewElements(false)); // Don't pan on color change

        // --- Listen for Livewire Events ---
        // Using Livewire's JS hooks if available, otherwise window events
        if (typeof Livewire !== 'undefined') {
            Livewire.hook('element.updated', (el) => {
                // Re-initialize map/listeners if the map container itself was re-rendered by Livewire (unlikely with wire:ignore)
                if (el.id === 'map' && !mapInitialized) {
                    console.warn('[Plotter] Map element updated by Livewire hook, re-initializing.');
                    initializeMap();
                }
            });

            // Listen for specific browser events dispatched by a Livewire component
            Livewire.on('points-updated', (event) => {
                // Standard Livewire v3 event handling (data is directly the first arg)
                console.log('[Plotter] Received points-updated event from Livewire:', event);
                updateMapPoints(event); // The event itself should be the array
            });

            Livewire.on('fly-to-point', (event) => {
                // Standard Livewire v3 event handling
                console.log('[Plotter] Received fly-to-point event from Livewire:', event);
                const { latitude, longitude, radius } = event[0];
                if (plotterMap && latitude != null && longitude != null) {
                    plotterMap.flyTo([latitude, longitude], calculateZoom(radius));
                }
            });

            // *** Update the coordinates-updated listener for immediate map update ***
            Livewire.on('coordinates-updated', (event) => {
                console.log('[Plotter] Received coordinates-updated event:', event);

                // Handle either event formats (array or direct object)
                const data = Array.isArray(event) ? event[0] : event;

                if (data && data.latitude && data.longitude) {
                    console.log('[Plotter] Processing coordinates from event:', data.latitude, data.longitude);

                    // IMPORTANT: Directly update preview marker and circle
                    if (previewMarker && previewCircle && plotterMap) {
                        const lat = parseFloat(data.latitude);
                        const lng = parseFloat(data.longitude);
                        const radius = parseInt(radiusInput?.value) || defaultRadius;
                        const color = colorInput?.value || defaultColor;

                        const latLng = [lat, lng];

                        // Update marker and circle positions
                        previewMarker.setLatLng(latLng);
                        previewCircle.setLatLng(latLng);
                        previewCircle.setRadius(radius);
                        previewCircle.setStyle({
                            color: color,
                            fillColor: color,
                        });

                        // Pan map to the new location
                        plotterMap.panTo(latLng);

                        console.log('[Plotter] Map preview directly updated with new coordinates');
                    }
                }
            });
        } else {
            // Fallback to window events if Livewire object not ready (less ideal)
            console.warn('[Plotter] Livewire global object not found, using window listeners as fallback.');
            window.addEventListener('points-updated', (event) => {
                console.log('[Plotter] Received points-updated window event:', event.detail);
                updateMapPoints(event.detail[0] || []); // Access data potentially nested under detail
            });
            window.addEventListener('fly-to-point', (event) => {
                console.log('[Plotter] Received fly-to-point window event:', event.detail);
                const { latitude, longitude, radius } = event.detail[0] || {}; // Access data potentially nested under detail
                if (plotterMap && latitude != null && longitude != null) {
                    plotterMap.flyTo([latitude, longitude], calculateZoom(radius));
                }
            });

            window.addEventListener('coordinates-updated', (event) => {
                console.log('[Plotter] Received coordinates-updated event:', event.detail);
                // Make sure detail exists and has the properties
                if (event.detail && event.detail.latitude != null && event.detail.longitude != null) {
                    const { latitude, longitude, formatted_address } = event.detail;

                    // Update form fields
                    if (latitudeInput) latitudeInput.value = latitude.toFixed(10);
                    if (longitudeInput) longitudeInput.value = longitude.toFixed(10);
                    if (labelInput && formatted_address) labelInput.value = formatted_address; // Update label too

                    // Dispatch input events AFTER setting value
                    if (latitudeInput) latitudeInput.dispatchEvent(new Event('input', { bubbles: true }));
                    if (longitudeInput) longitudeInput.dispatchEvent(new Event('input', { bubbles: true }));
                    if (labelInput) labelInput.dispatchEvent(new Event('input', { bubbles: true }));

                    // The input listeners will call updatePreviewElements, including panning the map.
                    // No need to call updatePreviewElements or panTo directly here.
                } else {
                    console.warn(
                        '[Plotter] coordinates-updated event received but detail is missing or invalid:',
                        event.detail,
                    );
                }
            });
        }
    }

    function updateMapPoints(points) {
        if (!plotterMap) return;
        console.log('[Plotter] Updating plotter map with points:', points);

        // Clear existing plotted layers (but not the preview layers)
        Object.values(plottedLayers).forEach((layerGroup) => layerGroup.remove());
        plottedLayers = {};

        const bounds = [];

        // Fix for nested array structure - normalize the points array
        let normalizedPoints = points;

        // Check if points is a nested array (array containing an array)
        if (Array.isArray(points) && points.length > 0 && Array.isArray(points[0])) {
            console.log('[Plotter] Detected nested array structure, flattening points array');
            normalizedPoints = points[0];
        }

        if (normalizedPoints && normalizedPoints.length > 0) {
            normalizedPoints.forEach((point, index) => {
                // Use index from the points array as ID if point.id isn't reliable
                const id = point.id ?? index;
                if (
                    point &&
                    point.latitude != null &&
                    point.longitude != null &&
                    point.radius != null &&
                    point.accuracy != null
                ) {
                    const latLng = [point.latitude, point.longitude];
                    const color = point.color || defaultColor;
                    const label = point.label || `Point ${id + 1}`; // Use index + 1 for unnamed points
                    const popupContent = `<strong>${label}</strong><br>
                                    Lat: ${point.latitude.toFixed(6)}<br>
                                    Lng: ${point.longitude.toFixed(6)}<br>
                                    Radius: ${point.radius}m<br>
                                    Accuracy: ${point.accuracy}m`;

                    const pointLayers = L.layerGroup();

                    // Radius Circle (Geofence)
                    L.circle(latLng, {
                        color: color,
                        fillColor: color,
                        fillOpacity: 0.5, // Slightly more opaque than preview
                        radius: point.radius,
                        weight: 1,
                    })
                        .bindPopup(popupContent)
                        .addTo(pointLayers);

                    // Center Marker (Pin)
                    L.marker(latLng, {
                        //
                    })
                        .bindPopup(popupContent)
                        .addTo(pointLayers);

                    pointLayers.addTo(plotterMap);
                    plottedLayers[id] = pointLayers; // Store by ID/index
                    bounds.push(latLng);

                    console.log(`[Plotter] Added point ${id} to map:`, point);
                } else {
                    console.warn('[Plotter] Skipping invalid point:', point);
                }
            });

            // Fit the map view to plotted points if any exist
            if (bounds.length > 0) {
                // Add slight padding to the bounds fit
                plotterMap.flyToBounds(bounds, { padding: [50, 50], maxZoom: 18 });
            }
        } else {
            console.log('[Plotter] No points to display.');
            // Reset view to default if all points are removed
            plotterMap.flyTo([defaultLat, defaultLng], defaultZoom);
        }
    }

    function calculateZoom(radius) {
        // Simple heuristic to determine zoom level based on radius
        if (radius <= 0) return 18; // Max zoom for zero/negative radius
        if (radius < 50) return 18;
        if (radius < 100) return 17;
        if (radius < 250) return 16;
        if (radius < 500) return 15;
        if (radius < 1000) return 14;
        if (radius < 2000) return 13;
        if (radius < 5000) return 12;
        if (radius < 10000) return 11;
        return 10; // Default zoom for very large radii
    }

    // --- Start Initialization ---
    initializeMap();
});

// Optional: Add handler for Livewire navigate event if using SPA-like navigation
// Needs careful testing to avoid double initialization
document.addEventListener('livewire:navigated', () => {
    console.log('[Plotter] Livewire navigated event detected.');
    const mapElement = document.getElementById('map');
    // We need a robust way to know if *this specific page's* map needs re-init
    // Checking if the map instance exists and belongs to the *current* element might work
    if (mapElement && mapElement.dataset.mapType === 'plotter') {
        /* && Add check if plotterMap is null or detached? */
        console.log('[Plotter] Re-initializing map after Livewire navigation.');
        // Potentially need cleanup logic here before re-initializing
    }
});
