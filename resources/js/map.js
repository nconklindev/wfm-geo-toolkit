import L from 'leaflet';

document.addEventListener('DOMContentLoaded', () => {
    const mapElement = document.getElementById('map');
    if (!mapElement) return;

    // Get form inputs (if they exist)
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const radiusInput = document.getElementById('radius');

    // Check if we're in edit mode (inputs exist) or display mode
    const isEditMode = latitudeInput && longitudeInput && radiusInput;

    // Initialize map with zoom controls in bottom left
    window.map = L.map('map', {
        zoomControl: false, // Disable default zoom control
    });

    // Add zoom control to bottom left
    L.control
        .zoom({
            position: 'bottomleft',
        })
        .addTo(window.map);

    // Add tile layer (OpenStreetMap)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(window.map);

    // Always load places if they're provided (for both modes)
    const placesJson = mapElement.dataset.places;
    const bounds = [];
    window.leafletMarkers = {};
    const placesById = {};

    if (placesJson) {
        try {
            const places = JSON.parse(placesJson);

            if (Array.isArray(places)) {
                places.forEach((place) => {
                    if (place && place.id && place.latitude && place.longitude && place.radius) {
                        placesById[place.id] = place;

                        // Store the circle in markers
                        window.leafletMarkers[place.id] = L.circle([place.latitude, place.longitude], {
                            color: '#4f46e5',
                            fillColor: '#818cf8',
                            fillOpacity: 0.5,
                            radius: place.radius,
                        })
                            .addTo(window.map)
                            .bindPopup(`<strong>${place.name}</strong><br>${place.description || ''}`);

                        bounds.push([place.latitude, place.longitude]);
                    }
                });
            } else {
                console.error('Places data is not an array:', places);
            }
        } catch (e) {
            console.error('Error parsing places data:', e);
            console.log('Raw data:', placesJson);
        }
    }

    // Handle edit mode functionality
    if (isEditMode) {
        // Default location (can be changed)
        let lat = 40.7128;
        let lng = -74.006;
        let radius = 75;

        // Use input values if they exist
        if (latitudeInput.value) {
            lat = parseFloat(latitudeInput.value);
        }

        if (longitudeInput.value) {
            lng = parseFloat(longitudeInput.value);
        }

        if (radiusInput.value) {
            radius = parseInt(radiusInput.value);
        }

        // Add marker for editing
        const marker = L.marker([lat, lng], {
            draggable: true,
        }).addTo(window.map);

        // Add circle to represent radius
        const circle = L.circle([lat, lng], {
            radius: radius,
            color: '#3b82f6',
            fillColor: '#3b82f6',
            fillOpacity: 0.2,
            weight: 2,
        }).addTo(window.map);

        // Update marker and circle when inputs change
        function updateMarkerAndCircle() {
            const newLat = parseFloat(latitudeInput.value) || lat;
            const newLng = parseFloat(longitudeInput.value) || lng;
            const newRadius = parseInt(radiusInput.value) || radius;

            marker.setLatLng([newLat, newLng]);
            circle.setLatLng([newLat, newLng]);
            circle.setRadius(newRadius);
            window.map.setView([newLat, newLng]);
        }

        // Update inputs when marker is dragged
        marker.on('dragend', function (e) {
            const position = marker.getLatLng();
            latitudeInput.value = position.lat.toFixed(6);
            longitudeInput.value = position.lng.toFixed(6);
            circle.setLatLng(position);
        });

        // Add event listeners to inputs
        latitudeInput.addEventListener('input', updateMarkerAndCircle);
        longitudeInput.addEventListener('input', updateMarkerAndCircle);
        radiusInput.addEventListener('input', updateMarkerAndCircle);

        // Listen for coordinates-updated event from Livewire component
        window.addEventListener('coordinates-updated', (event) => {
            const coords = event.detail[0];

            // Only update if we have valid coordinates
            if (coords && coords.latitude && coords.longitude) {
                latitudeInput.value = coords.latitude;
                longitudeInput.value = coords.longitude;

                // Update the marker and circle
                updateMarkerAndCircle();

                // Fly to the new location with animation
                window.map.flyTo([coords.latitude, coords.longitude], 15);
            }
        });

        // Set view to the edit marker
        window.map.setView([lat, lng], 15);

        // Also add this location to bounds if we're showing other places
        bounds.push([lat, lng]);
    }

    // Set map view based on available data
    if (bounds.length > 0) {
        window.map.fitBounds(bounds);
    } else {
        window.map.setView([0, 0], 2); // Default world view
    }
});
