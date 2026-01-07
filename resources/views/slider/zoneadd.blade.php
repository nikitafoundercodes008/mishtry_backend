@extends('layouts.app')
@section('app')

<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 34px;
        height: 20px;
    }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 50px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 12px; width: 12px;
        border-radius: 50px;
        left: 4px; bottom: 4px;
        background-color: white;
        transition: .4s;
    }
    input:checked + .slider { background-color: #2196F3; }
    input:checked + .slider:before { transform: translateX(14px); }
    .map-container {
        position: relative;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        overflow: hidden;
    }
    .map-tools {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 1000;
        background: white;
        padding: 8px;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
</style>

<div class="pagetitle">
    <h1>Create Zone</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ url('zones') }}">Zones</a></li>
            <li class="breadcrumb-item active">Create Zone</li>
        </ol>
    </nav>
</div>

<div class="card p-4">
    <form action="{{ route('zones.store') }}" method="POST">
        @csrf

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <h4 class="mb-3">Zone Details</h4>

                <div class="mb-3">
                    <label class="form-label">Zone Name *</label>
                    <input type="text" name="zone_name" class="form-control" required 
                           placeholder="Add your zone name" value="{{ old('zone_name') }}">
                    <div class="form-text">Enter a unique name for this zone.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" 
                              placeholder="Add description (optional)">{{ old('description') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label><br>
                    <label class="switch">
                        <input type="checkbox" name="status" value="1" {{ old('status', 1) ? 'checked' : '' }}>
                        <span class="slider round"></span>
                    </label>
                    <span class="ms-2">Active</span>
                </div>

                <!-- Hidden fields for coordinates -->
                <input type="hidden" name="coordinates" id="coordinates" value="{{ old('coordinates') }}">
                <input type="hidden" name="center_lat" id="center_lat" value="{{ old('center_lat') }}">
                <input type="hidden" name="center_lng" id="center_lng" value="{{ old('center_lng') }}">
                <input type="hidden" name="area_sqkm" id="area_sqkm" value="{{ old('area_sqkm') }}">

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Save Zone
                    </button>
                    <a href="{{ url('zones') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Cancel
                    </a>
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Search Location</label>
                    <input type="text" id="searchBox" class="form-control" 
                           placeholder="Search for a city or address">
                </div>

                <div class="map-container">
                    <div id="map" style="height: 350px; width:100%;"></div>
                    <div class="map-tools">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="drawPolygon">
                                <i class="bi bi-pencil"></i> Draw
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="clearPolygon">
                                <i class="bi bi-trash"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Coordinates Preview -->
                <div class="mt-3">
                    <label class="form-label">Selected Area Info</label>
                    <div id="areaInfo" class="alert alert-info p-2">
                        <small>
                            <div>Coordinates: <span id="coordCount">0</span> points</div>
                            <div>Center: <span id="centerCoord">Not set</span></div>
                            <div>Area: <span id="areaValue">0</span> sq km</div>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <hr class="mt-4">

        <div class="row">
            <div class="col-12">
                <h5><i class="bi bi-info-circle text-primary"></i> Instructions</h5>
                <div class="row">
                    <div class="col-md-3">
                        <p><i class="bi bi-hand-index"></i> <strong>Move Map:</strong> Click and drag</p>
                    </div>
                    <div class="col-md-3">
                        <p><i class="bi bi-plus-circle"></i> <strong>Draw Polygon:</strong> Click points to create area</p>
                    </div>
                    <div class="col-md-3">
                        <p><i class="bi bi-check-circle"></i> <strong>Complete:</strong> Click first point or double-click</p>
                    </div>
                    <div class="col-md-3">
                        <p><i class="bi bi-trash"></i> <strong>Clear:</strong> Click trash button to remove</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyANhzkw-SjvdzDvyPsUBDFmvEHfI9b8QqA&libraries=places,drawing,geometry"></script>

<script>
let map;
let drawingManager;
let selectedShape = null;
let polygons = [];

function initMap() {
    // Initialize map with default center (Delhi)
    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 10,
        center: { lat: 28.6139, lng: 77.2090 },
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        disableDefaultUI: false,
        zoomControl: true,
        streetViewControl: false,
        fullscreenControl: true
    });

    // Initialize search box
    const input = document.getElementById("searchBox");
    const searchBox = new google.maps.places.SearchBox(input);
    map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

    searchBox.addListener("places_changed", function() {
        const places = searchBox.getPlaces();
        if (!places.length) return;

        const place = places[0];
        if (place.geometry) {
            map.setCenter(place.geometry.location);
            map.setZoom(14);
        }
    });

    // Initialize drawing manager
    drawingManager = new google.maps.drawing.DrawingManager({
        drawingControl: false, // We'll use custom controls
        drawingMode: null,
        markerOptions: { draggable: false },
        polygonOptions: {
            fillColor: '#2196F3',
            fillOpacity: 0.2,
            strokeWeight: 2,
            strokeColor: '#2196F3',
            editable: true,
            draggable: false
        }
    });

    drawingManager.setMap(map);

    // Listen for polygon complete event
    google.maps.event.addListener(drawingManager, 'polygoncomplete', function(polygon) {
        if (selectedShape) {
            selectedShape.setMap(null);
            polygons = [];
        }
        
        selectedShape = polygon;
        polygons.push(polygon);
        updateCoordinates(polygon);
        
        // Listen for polygon edits
        polygon.getPath().addListener('set_at', function() {
            updateCoordinates(polygon);
        });
        
        polygon.getPath().addListener('insert_at', function() {
            updateCoordinates(polygon);
        });
        
        polygon.addListener('click', function() {
            // Polygon clicked
        });
    });

    // Custom draw button
    document.getElementById('drawPolygon').addEventListener('click', function() {
        drawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYGON);
        this.classList.remove('btn-outline-primary');
        this.classList.add('btn-primary');
    });

    // Clear polygon button
    document.getElementById('clearPolygon').addEventListener('click', function() {
        if (selectedShape) {
            selectedShape.setMap(null);
            selectedShape = null;
            polygons = [];
            
            document.getElementById('coordinates').value = '';
            document.getElementById('center_lat').value = '';
            document.getElementById('center_lng').value = '';
            document.getElementById('area_sqkm').value = '';
            
            updateAreaInfo();
            
            drawingManager.setDrawingMode(null);
            document.getElementById('drawPolygon').classList.remove('btn-primary');
            document.getElementById('drawPolygon').classList.add('btn-outline-primary');
        }
    });

    // When drawing mode changes
    google.maps.event.addListener(drawingManager, 'drawingmode_changed', function() {
        if (drawingManager.getDrawingMode() === google.maps.drawing.OverlayType.POLYGON) {
            document.getElementById('drawPolygon').classList.remove('btn-outline-primary');
            document.getElementById('drawPolygon').classList.add('btn-primary');
        } else {
            document.getElementById('drawPolygon').classList.remove('btn-primary');
            document.getElementById('drawPolygon').classList.add('btn-outline-primary');
        }
    });

    // When polygon is completed
    google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
        drawingManager.setDrawingMode(null);
        document.getElementById('drawPolygon').classList.remove('btn-primary');
        document.getElementById('drawPolygon').classList.add('btn-outline-primary');
    });

    // Load existing coordinates if any
    const existingCoords = document.getElementById('coordinates').value;
    if (existingCoords) {
        try {
            const coords = JSON.parse(existingCoords);
            drawExistingPolygon(coords);
        } catch(e) {
            console.error('Error parsing coordinates:', e);
        }
    }
}

function updateCoordinates(polygon) {
    const path = polygon.getPath();
    const coordinates = [];
    
    for (let i = 0; i < path.getLength(); i++) {
        const p = path.getAt(i);
        coordinates.push({
            lat: p.lat(),
            lng: p.lng()
        });
    }
    
    // Calculate center
    const bounds = new google.maps.LatLngBounds();
    coordinates.forEach(coord => {
        bounds.extend(new google.maps.LatLng(coord.lat, coord.lng));
    });
    const center = bounds.getCenter();
    
    // Calculate area
    const area = google.maps.geometry.spherical.computeArea(path);
    const areaSqKm = area / 1000000; // Convert to square kilometers
    
    // Update hidden fields
    document.getElementById('coordinates').value = JSON.stringify(coordinates);
    document.getElementById('center_lat').value = center.lat();
    document.getElementById('center_lng').value = center.lng();
    document.getElementById('area_sqkm').value = areaSqKm.toFixed(2);
    
    // Update area info display
    updateAreaInfo();
}

function updateAreaInfo() {
    const coords = document.getElementById('coordinates').value;
    const centerLat = document.getElementById('center_lat').value;
    const centerLng = document.getElementById('center_lng').value;
    const area = document.getElementById('area_sqkm').value;
    
    if (coords) {
        try {
            const coordArray = JSON.parse(coords);
            document.getElementById('coordCount').textContent = coordArray.length;
        } catch(e) {
            document.getElementById('coordCount').textContent = '0';
        }
    } else {
        document.getElementById('coordCount').textContent = '0';
    }
    
    document.getElementById('centerCoord').textContent = 
        centerLat && centerLng ? `${parseFloat(centerLat).toFixed(4)}, ${parseFloat(centerLng).toFixed(4)}` : 'Not set';
    
    document.getElementById('areaValue').textContent = area || '0';
}

function drawExistingPolygon(coordinates) {
    const polygon = new google.maps.Polygon({
        paths: coordinates,
        strokeColor: '#2196F3',
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: '#2196F3',
        fillOpacity: 0.2,
        editable: true,
        draggable: false
    });
    
    polygon.setMap(map);
    selectedShape = polygon;
    polygons.push(polygon);
    
    // Fit map to polygon bounds
    const bounds = new google.maps.LatLngBounds();
    coordinates.forEach(coord => {
        bounds.extend(new google.maps.LatLng(coord.lat, coord.lng));
    });
    map.fitBounds(bounds);
    
    // Add listeners for editing
    polygon.getPath().addListener('set_at', function() {
        updateCoordinates(polygon);
    });
    
    polygon.getPath().addListener('insert_at', function() {
        updateCoordinates(polygon);
    });
    
    updateCoordinates(polygon);
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const zoneName = document.querySelector('input[name="zone_name"]').value.trim();
    const coordinates = document.getElementById('coordinates').value;
    
    if (!zoneName) {
        e.preventDefault();
        alert('Please enter zone name');
        return;
    }
    
    if (!coordinates) {
        e.preventDefault();
        alert('Please draw a zone area on the map');
        return;
    }
    
    // Validate coordinates format
    try {
        const coords = JSON.parse(coordinates);
        if (!Array.isArray(coords) || coords.length < 3) {
            e.preventDefault();
            alert('Zone area must have at least 3 points');
            return;
        }
    } catch(err) {
        e.preventDefault();
        alert('Invalid coordinates format');
        return;
    }
    
    // Show loading
    const submitBtn = document.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
});

// Initialize map when window loads
window.onload = initMap;
</script>

@endsection