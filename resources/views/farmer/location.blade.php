@extends('layouts.dashboard', ['role' => 'farmer'])
@section('title', 'Farm Location')
@push('styles')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endpush
@section('content')
<div class="layout-2" style="grid-template-columns:1fr 360px">
    <div class="stack gap-12">
        <div id="farm-map" role="application" aria-label="Pick your farm location on the map"></div>
        <p class="muted" style="margin:0">Click the map or drag the pin to the exact spot of your farm. Only points inside Bangladesh are accepted.</p>
    </div>
    <form class="card" method="POST" action="{{ route('farmer.location.save') }}" style="align-self:start">
        @csrf
        <h3 class="mb-16">Farm Location</h3>
        <div class="form-group"><label for="division">Division</label><input id="division" value="" readonly></div>
        <div class="form-group"><label for="district">District</label>@include('partials.district-select', ['districts' => $districts, 'selected' => old('district', $farm['district'] ?? ''), 'required' => true])</div>
        <div class="form-group"><label for="upazila">Upazila</label><input id="upazila" name="upazila" value="{{ old('upazila', $farm['upazila'] ?? '') }}" placeholder="e.g. Bogura Sadar" required maxlength="80"></div>
        <div class="form-row">
            <div class="form-group"><label for="lat">Latitude</label><input id="lat" name="latitude" value="{{ old('latitude', $farm['latitude'] ?? '') }}" readonly></div>
            <div class="form-group"><label for="lng">Longitude</label><input id="lng" name="longitude" value="{{ old('longitude', $farm['longitude'] ?? '') }}" readonly></div>
        </div>
        <button class="btn btn-block" type="submit">Save Location</button>
        <p class="muted mt-16" style="margin-bottom:0">Clients in your district will see your products first.</p>
    </form>
</div>
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var districts = @json($districts);
    var savedLat = {{ (float) ($farm['latitude'] ?? 0) }}, savedLng = {{ (float) ($farm['longitude'] ?? 0) }};
    var sel = document.getElementById('district'), lat = document.getElementById('lat'), lng = document.getElementById('lng'), div = document.getElementById('division');
    function current() { return districts.find(function (d) { return d.name === sel.value; }) || districts[0]; }
    var start = current(), hasPoint = savedLat !== 0 && savedLng !== 0;
    var map = L.map('farm-map').setView(hasPoint ? [savedLat, savedLng] : [start.lat, start.lng], hasPoint ? 12 : 10);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 18, attribution: '&copy; OpenStreetMap contributors'}).addTo(map);
    var marker = L.marker(hasPoint ? [savedLat, savedLng] : [start.lat, start.lng], {draggable: true}).addTo(map);
    function setPoint(la, ln) { lat.value = la.toFixed(6); lng.value = ln.toFixed(6); }
    div.value = start.division; setPoint(hasPoint ? savedLat : start.lat, hasPoint ? savedLng : start.lng);
    marker.on('dragend', function () { var p = marker.getLatLng(); setPoint(p.lat, p.lng); });
    map.on('click', function (e) { marker.setLatLng(e.latlng); setPoint(e.latlng.lat, e.latlng.lng); });
    sel.addEventListener('change', function () { var d = current(); div.value = d.division; map.setView([d.lat, d.lng], 10); marker.setLatLng([d.lat, d.lng]); setPoint(d.lat, d.lng); });
</script>
@endpush
