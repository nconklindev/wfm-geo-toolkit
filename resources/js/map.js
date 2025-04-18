import L from 'leaflet';

let mapInitialized = false;
let editModeCircle = null;
let currentMapInstance = null; // Use a module-level variable for the map instance

// Function to safely remove the map
function removeMapInstance() {
    if (currentMapInstance) {
        console.log('Removing existing map instance.');
        try {
            currentMapInstance.remove();
        } catch (e) {
            console.error('Error removing map instance:', e);
        } finally {
            currentMapInstance = null;
            editModeCircle = null;
            mapInitialized = false;
            // Also clear the Leaflet internal ID from the element if it exists
            const mapElement = document.getElementById('map');
            if (mapElement && mapElement._leaflet_id) {
                delete mapElement._leaflet_id;
                console.log('Cleared Leaflet ID from map element.');
            }
        }
    }
}

// The core map initialization logic
function setupMap() {
    const mapElement = document.getElementById('map');
    if (!mapElement) {
        console.log('Map element not found. Aborting setup.');
        removeMapInstance(); // Clean up if the element disappeared
        return;
    }

    // If a map instance somehow still exists or the element has ID, remove before creating new
    if (currentMapInstance || mapElement._leaflet_id) {
        console.log('Map instance or element ID found unexpectedly. Forcing removal.');
        removeMapInstance();
        // Use setTimeout to allow DOM to potentially settle after removal
        setTimeout(setupMap, 50); // Retry setup shortly
        return;
    }

    // Prevent double initialization attempts closely spaced
    if (mapInitialized) {
        console.log('Map setup already in progress or completed. Aborting.');
        if (currentMapInstance) {
            try {
                currentMapInstance.invalidateSize();
            } catch (e) {
                /* ignore */
            }
        }
        return;
    }

    mapInitialized = true; // Set the flag early to prevent race conditions
    console.log('Proceeding with new map instance creation...');

    // --- Get form inputs (the rest of the function is similar to before) ---
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const radiusInput = document.getElementById('radius');
    const colorInput = document.getElementById('color-picker');

    const isEditMode = latitudeInput && longitudeInput && radiusInput && colorInput;

    try {
        currentMapInstance = L.map('map', {
            zoomControl: false,
        });
        console.log('L.map created successfully.');

        L.control.zoom({ position: 'bottomleft' }).addTo(currentMapInstance);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(currentMapInstance);

        const placesJson = mapElement.dataset.places;
        const bounds = [];
        window.leafletMarkers = {}; // Reset markers

        if (placesJson) {
            try {
                const places = JSON.parse(placesJson);
                const placesToProcess = Array.isArray(places) ? places : [places];

                placesToProcess.forEach((place) => {
                    if (place && place.id && place.latitude && place.longitude && place.radius) {
                        const latLng = [place.latitude, place.longitude];
                        const displayColor = place.color || '#4f46e5'; // Default- or place-specific color
                        const popupContent = `<strong>${place.name}</strong><br>${place.description || ''}`;

                        // --- Add the Circle ---
                        window.leafletMarkers[place.id] = L.circle(latLng, {
                            color: displayColor,
                            fillColor: displayColor,
                            fillOpacity: 0.5,
                            radius: place.radius,
                            weight: 1, // Thin border for the display view
                        })
                            .addTo(currentMapInstance)
                            .bindPopup(popupContent);

                        // --- Add the Marker (Pin) ---
                        // Add a non-draggable marker at the center for display views
                        L.marker(latLng, {
                            draggable: false, // Ensure it's not draggable on show page
                            // Optional: Use a custom icon if desired
                            // icon: L.icon({ ... })
                        })
                            .addTo(currentMapInstance)
                            .bindPopup(popupContent); // Bind the same popup for consistency

                        bounds.push(latLng);
                    } else {
                        console.warn('Skipping invalid place:', place);
                    }
                });
                console.log(`Processed ${placesToProcess.length} places.`);
            } catch (e) {
                console.error('Error parsing places data:', e);
                if (currentMapInstance) currentMapInstance.setView([0, 0], 2);
            }
        } else {
            console.log('No placesJson data found.');
        }

        if (isEditMode) {
            console.log('Setting up Edit Mode.');
            let lat = 40.7128;
            let lng = -74.006;
            let radius = 75;
            let initialColor = '#3b82f6';

            if (latitudeInput.value && !isNaN(parseFloat(latitudeInput.value))) lat = parseFloat(latitudeInput.value);
            if (longitudeInput.value && !isNaN(parseFloat(longitudeInput.value)))
                lng = parseFloat(longitudeInput.value);
            if (radiusInput.value && !isNaN(parseInt(radiusInput.value))) radius = parseInt(radiusInput.value);
            if (colorInput.value && /^#([0-9A-F]{3}){1,2}$/i.test(colorInput.value)) {
                initialColor = colorInput.value;
            } else if (colorInput.value) {
                console.warn(`Invalid initial color value "${colorInput.value}", using default.`);
                colorInput.value = initialColor;
            }

            const marker = L.marker([lat, lng], { draggable: true }).addTo(currentMapInstance);
            editModeCircle = L.circle([lat, lng], {
                radius: radius,
                color: initialColor,
                fillColor: initialColor,
                fillOpacity: 0.3,
                weight: 2,
            }).addTo(currentMapInstance);

            function updateMarkerAndCircle() {
                if (!currentMapInstance) return; // Check map exists
                const newLat = parseFloat(latitudeInput.value) || lat;
                const newLng = parseFloat(longitudeInput.value) || lng;
                const newRadius = parseInt(radiusInput.value) || radius;
                const newColor = colorInput.value || initialColor;

                marker.setLatLng([newLat, newLng]);
                if (editModeCircle) {
                    editModeCircle.setLatLng([newLat, newLng]);
                    editModeCircle.setRadius(newRadius);
                    if (/^#([0-9A-F]{3}){1,2}$/i.test(newColor)) {
                        editModeCircle.setStyle({ color: newColor, fillColor: newColor });
                    } else {
                        console.warn(`Skipping invalid color update: "${newColor}"`);
                    }
                }
                currentMapInstance.setView([newLat, newLng]);
            }

            marker.on('dragend', function (e) {
                const position = marker.getLatLng();
                latitudeInput.value = position.lat.toFixed(6);
                longitudeInput.value = position.lng.toFixed(6);
                latitudeInput.dispatchEvent(new Event('input'));
                longitudeInput.dispatchEvent(new Event('input'));
                if (editModeCircle) editModeCircle.setLatLng(position);
            });

            latitudeInput.addEventListener('input', updateMarkerAndCircle);
            longitudeInput.addEventListener('input', updateMarkerAndCircle);
            radiusInput.addEventListener('input', updateMarkerAndCircle);
            colorInput.addEventListener('input', () => {
                if (!editModeCircle) return;
                const newColor = colorInput.value;
                if (/^#([0-9A-F]{3}){1,2}$/i.test(newColor)) {
                    editModeCircle.setStyle({ color: newColor, fillColor: newColor });
                } else {
                    console.warn(`Invalid color format: "${newColor}". Circle color not updated.`);
                }
            });

            // Use a WeakMap to track if a listener was added to avoid duplicates
            if (!window.coordUpdateListenerAdded) {
                window.coordUpdateListenerAdded = new WeakMap();
            }
            if (!window.coordUpdateListenerAdded.has(window)) {
                window.addEventListener('coordinates-updated', (event) => {
                    // Ensure the map instance is still valid inside the listener
                    if (!currentMapInstance || !event.detail || !event.detail[0]) return;
                    const coords = event.detail[0];
                    if (coords && coords.latitude && coords.longitude) {
                        latitudeInput.value = coords.latitude;
                        longitudeInput.value = coords.longitude;
                        latitudeInput.dispatchEvent(new Event('input'));
                        longitudeInput.dispatchEvent(new Event('input'));
                        updateMarkerAndCircle(); // updateMarkerAndCircle already checks currentMapInstance
                        currentMapInstance.flyTo([coords.latitude, coords.longitude], 15);
                    }
                });
                window.coordUpdateListenerAdded.set(window, true);
                console.log('Added coordinates-updated listener.');
            }

            bounds.push([lat, lng]); // Add edit location to bounds
            currentMapInstance.setView([lat, lng], 15); // Set the initial view for edit mode
        } // End of isEditMode block

        // Set the map view based on bounds AFTER processing places and potentially edit mode
        if (bounds.length > 0 && currentMapInstance) {
            console.log(`Fitting map to ${bounds.length} bounds.`);
            try {
                currentMapInstance.fitBounds(bounds, { padding: [50, 50] });
            } catch (e) {
                console.error('Error fitting map bounds:', e);
                if (bounds.length === 1 && currentMapInstance) {
                    currentMapInstance.setView(bounds[0], isEditMode ? 15 : 13);
                } else if (currentMapInstance) {
                    currentMapInstance.setView([0, 0], 2);
                }
            }
        } else if (currentMapInstance && !isEditMode) {
            // Added !isEditMode condition
            // Fallback view if no places and not in edit mode
            console.log('No bounds to fit and not in edit mode, setting default view.');
            currentMapInstance.setView([0, 0], 2); // Default world view
        } else if (isEditMode && currentMapInstance && bounds.length === 1) {
            // This case is usually handled by the setView inside isEditMode, but as a fallback
            console.log('Setting map view to edit marker (isEditMode, bounds=1).');
            currentMapInstance.setView(bounds[0], 15);
        }

        console.log('Map instance created and configured.');

        // Finally, invalidate size check
        setTimeout(() => {
            if (currentMapInstance) {
                try {
                    currentMapInstance.invalidateSize();
                    console.log('Final invalidateSize complete.');
                } catch (e) {
                    console.warn('Error invalidating map size on timeout:', e);
                }
            }
        }, 150); // Slightly longer delay after all setup
    } catch (error) {
        console.error('!!! Critical error during map setup:', error);
        mapInitialized = false; // Reset flag on critical failure
        // Attempt cleanup if the map object was partially created
        if (currentMapInstance) {
            removeMapInstance();
        }
    }
}

// --- Initialization Trigger Logic ---

// Function to attempt initialization
function tryInitializeMap() {
    const mapElement = document.getElementById('map');
    // Only proceed if the map element exists AND it doesn't already have a map initialized
    // (checking internal _leaflet_id is a good indicator)
    if (mapElement && !mapElement._leaflet_id) {
        console.log('Map element found and seems uninitialized. Calling setupMap.');
        // Remove any lingering instance first, just in case the state is inconsistent
        removeMapInstance();
        // Use requestAnimationFrame for smoother rendering timing
        requestAnimationFrame(setupMap);
        // setupMap(); // Or call directly if rAF causes issues
    } else if (mapElement && mapElement._leaflet_id) {
        console.log('Map element found, but already initialized (_leaflet_id exists). Skipping setup.');
        // Ensure the size is correct if the map instance exists
        if (currentMapInstance) {
            requestAnimationFrame(() => {
                try {
                    currentMapInstance.invalidateSize();
                } catch (e) {
                    /* ignore */
                }
            });
        }
    } else {
        console.log('Map element not found when tryInitializeMap was called.');
        // Clean up any potentially orphaned map instance
        removeMapInstance();
    }
}

// Store listener references to prevent duplicates if the script runs multiple times
if (!window.mapInitListenersAdded) {
    console.log('Adding map initialization event listeners.');
    document.addEventListener('DOMContentLoaded', () => {
        console.log('DOM Content Loaded event - Trying map init.');
        tryInitializeMap();
    });

    document.addEventListener('livewire:navigated', () => {
        console.log('Livewire Navigated event - Trying map init.');
        tryInitializeMap();
    });

    window.mapInitListenersAdded = true;
} else {
    console.log('Map initialization event listeners already added.');
}

// Exporting setupMap might be useful for manual re-init if needed elsewhere
export { setupMap as initializeMap }; // Rename export to keep consistency
// Expose globally ONLY if absolutely necessary for debugging or direct calls
// window.initializeMap = tryInitializeMap;
