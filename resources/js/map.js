import L from 'leaflet';

document.addEventListener('DOMContentLoaded', () => {
    const mapElement = document.getElementById('map');

    if (!mapElement) return;

    // Get input values
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const radiusInput = document.getElementById('radius');

    // Default location (can be changed)
    let lat = 40.7128;
    let lng = -74.0060;
    let radius = 75;

    // Use input values if they exist
    if (latitudeInput && latitudeInput.value) {
        lat = parseFloat(latitudeInput.value);
    }

    if (longitudeInput && longitudeInput.value) {
        lng = parseFloat(longitudeInput.value);
    }

    if (radiusInput && radiusInput.value) {
        radius = parseInt(radiusInput.value);
    }

    // Initialize map with zoom controls in bottom left
    const map = L.map('map', {
        zoomControl: false // Disable default zoom control
    }).setView([lat, lng], 15);

    // Add zoom control to bottom left
    L.control.zoom({
        position: 'bottomleft'
    }).addTo(map);


    // Add tile layer (OpenStreetMap)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Add marker
    const marker = L.marker([lat, lng], {
        draggable: true
    }).addTo(map);

    // Add circle to represent radius
    const circle = L.circle([lat, lng], {
        radius: radius,
        color: '#3b82f6',
        fillColor: '#3b82f6',
        fillOpacity: 0.2,
        weight: 2
    }).addTo(map);

    // Update marker and circle when inputs change
    function updateMarkerAndCircle() {
        const newLat = parseFloat(latitudeInput.value) || lat;
        const newLng = parseFloat(longitudeInput.value) || lng;
        const newRadius = parseInt(radiusInput.value) || radius;

        marker.setLatLng([newLat, newLng]);
        circle.setLatLng([newLat, newLng]);
        circle.setRadius(newRadius);
        map.setView([newLat, newLng]);
    }

    // Update inputs when marker is dragged
    marker.on('dragend', function (e) {
        const position = marker.getLatLng();
        if (latitudeInput) latitudeInput.value = position.lat.toFixed(6);
        if (longitudeInput) longitudeInput.value = position.lng.toFixed(6);
        circle.setLatLng(position);
    });

    // Add event listeners to inputs
    if (latitudeInput) latitudeInput.addEventListener('input', updateMarkerAndCircle);
    if (longitudeInput) longitudeInput.addEventListener('input', updateMarkerAndCircle);
    if (radiusInput) radiusInput.addEventListener('input', updateMarkerAndCircle);

    // Listen for coordinates-updated event from Livewire component
    window.addEventListener('coordinates-updated', (event) => {
        const coords = event.detail[0];

        // Only update if we have valid coordinates
        if (coords && coords.latitude && coords.longitude) {
            if (latitudeInput) latitudeInput.value = coords.latitude;
            if (longitudeInput) longitudeInput.value = coords.longitude;

            // Update the marker and circle
            updateMarkerAndCircle();

            // Fly to the new location with animation
            map.flyTo([coords.latitude, coords.longitude], 15);
        }
    });
});
