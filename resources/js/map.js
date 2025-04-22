import L from 'leaflet';

let mapInitialized = false;
let editModeCircle = null;
let currentMapInstance = null; // Use a module-level variable for the map instance
// Add this at the top of your file, near other global variables
let currentMarker = null;

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
    const colorInput = document.getElementById('color');

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

        // Then in the setupMap function, modify the marker creation:
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

            // Change this line to set the global marker
            currentMarker = L.marker([lat, lng], { draggable: true }).addTo(currentMapInstance);

            editModeCircle = L.circle([lat, lng], {
                radius: radius,
                color: initialColor,
                fillColor: initialColor,
                fillOpacity: 0.3,
                weight: 2,
            }).addTo(currentMapInstance);

            // Update updateMarkerAndCircle function to use the currentMarker
            function updateMarkerAndCircle(shouldSetView = true) {
                // Add parameter with default true
                if (!currentMapInstance) return; // Check map exists
                const newLat = parseFloat(latitudeInput.value) || lat;
                const newLng = parseFloat(longitudeInput.value) || lng;
                const newRadius = parseInt(radiusInput.value) || radius;
                const newColor = colorInput.value || initialColor;

                currentMarker.setLatLng([newLat, newLng]);
                currentMarker.dragging.enable(); // Ensure dragging is enabled

                if (editModeCircle) {
                    editModeCircle.setLatLng([newLat, newLng]);
                    editModeCircle.setRadius(newRadius);
                    if (/^#([0-9A-F]{3}){1,2}$/i.test(newColor)) {
                        editModeCircle.setStyle({ color: newColor, fillColor: newColor });
                    } else {
                        console.warn(`Skipping invalid color update: "${newColor}"`);
                    }
                }
                // Only set the view if requested (defaults to true)
                if (shouldSetView) {
                    currentMapInstance.setView([newLat, newLng]);
                }
            }

            currentMarker.on('dragend', function () {
                const position = currentMarker.getLatLng();
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

            if (!window.coordUpdateListenerAdded) {
                window.coordUpdateListenerAdded = new WeakMap();
            }
            if (!window.coordUpdateListenerAdded.has(window)) {
                window.addEventListener('coordinates-updated', (event) => {
                    // Ensure the map instance and detail are valid
                    if (!currentMapInstance || !event.detail) {
                        console.warn('Coordinates updated event ignored: missing map instance or event detail.');
                        return;
                    }

                    console.log('Received coordinates-updated event detail:', event.detail); // Keep this for debugging

                    // Determine the coordinate data, handling both object and array[0] cases
                    let coords;
                    if (Array.isArray(event.detail) && event.detail.length > 0) {
                        // It's an array containing the object
                        coords = event.detail[0];
                        console.log('Detected array format, using event.detail[0]');
                    } else if (
                        typeof event.detail === 'object' &&
                        event.detail !== null &&
                        !Array.isArray(event.detail)
                    ) {
                        // It's a direct object
                        coords = event.detail;
                        console.log('Detected direct object format, using event.detail');
                    } else {
                        console.warn(
                            'Coordinates updated event ignored: event.detail format is unexpected.',
                            event.detail,
                        );
                        return;
                    }

                    // Check if the expected properties exist on the coords object
                    if (coords && typeof coords.latitude !== 'undefined' && typeof coords.longitude !== 'undefined') {
                        console.log('Processing coordinates:', coords);

                        const newLat = parseFloat(coords.latitude);
                        const newLng = parseFloat(coords.longitude);

                        // Check if parsing was successful before updating
                        if (!isNaN(newLat) && !isNaN(newLng)) {
                            // Update input fields (ensure they exist)
                            const latitudeInput = document.getElementById('latitude');
                            const longitudeInput = document.getElementById('longitude');
                            if (latitudeInput) latitudeInput.value = newLat.toFixed(6);
                            if (longitudeInput) longitudeInput.value = newLng.toFixed(6);

                            // *** THIS IS THE CRUCIAL PART FOR PLOTTING ***
                            // Update marker/circle AND set the map view
                            updateMarkerAndCircle(true); // Pass true to set the view

                            if (currentMarker) currentMarker.dragging.enable();
                            console.log('Marker set to draggable');
                        } else {
                            console.warn('Failed to parse coordinates:', coords.latitude, coords.longitude);
                        }
                    } else {
                        console.warn(
                            'Received coordinates-updated event, but determined coords object lacked lat/lon:',
                            coords,
                        );
                    }
                });
                // Set the flag after adding the listener
                window.coordUpdateListenerAdded.set(window, true);
            }

            // Initial view setup
            if (editModeCircle || bounds.length > 0) {
                if (editModeCircle) {
                    currentMapInstance.setView([lat, lng], 15); // Zoom in closer for edit mode
                } else {
                    currentMapInstance.fitBounds(bounds, { padding: [50, 50] });
                }
            } else {
                currentMapInstance.setView([lat, lng], 13); // Default view if nothing else
            }
        } else {
            // Handle non-edit mode map setup (if bounds exist, fit them)
            if (bounds.length > 0) {
                currentMapInstance.fitBounds(bounds, { padding: [50, 50] });
            } else {
                // Default view if no places and not in edit mode
                currentMapInstance.setView([0, 0], 2); // Sensible default, e.g., world view
            }
        }

        // Ensure the map size is correct after initialization / potential redrawing
        setTimeout(() => {
            if (currentMapInstance) {
                console.log('Invalidating map size...');
                currentMapInstance.invalidateSize();
            }
        }, 100); // Short delay

        console.log('Map setup finished.');
    } catch (error) {
        console.error('Error during map setup:', error);
        mapInitialized = false; // Reset flag on error
        removeMapInstance(); // Attempt cleanup on error
    }
}

// --- Event Listeners ---

// Use DOMContentLoaded for initial setup
document.addEventListener('DOMContentLoaded', setupMap);

document.addEventListener('livewire:navigated', () => {
    console.log('Livewire navigated event detected.');
    removeMapInstance(); // Ensure the old map is gone before setting up new
    setupMap(); // Re-initialize map on navigation
});

// Add a resize listener to handle map resizing issues
let resizeTimeout;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        if (currentMapInstance) {
            console.log('Window resized, invalidating map size.');
            currentMapInstance.invalidateSize();
        }
    }, 250); // Debounce resize events
});
