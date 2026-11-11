@extends('layouts.public')
@section('title', 'Shop by District')
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush
@section('content')
@include('partials.page-header', ['title' => 'Shop by District', 'subtitle' => 'Choose your district to buy fresh products from farmers near you'])
<div class="container section">
    @php $districtProductCounts = collect($products)->countBy('district'); @endphp
    <div class="district-shop-layout">
        <section class="district-map-panel">
            <div class="district-panel-heading">
                <div><span class="district-eyebrow"><i class="bi bi-geo-alt"></i> NEAR YOU</span><h2>Explore local farms</h2><p>Choose a district on the map or search the list.</p></div>
                <span class="district-panel-icon"><i class="bi bi-map"></i></span>
            </div>
            <div id="district-map" role="application" aria-label="Map of Bangladesh districts"></div>
            <div class="district-map-legend"><span><i></i>District markets</span><span>Tap a marker to browse products</span></div>
        </section>
        <section class="card district-browser">
            <div class="district-browser-head"><div><span class="district-eyebrow">BANGLADESH</span><h2>Find your district</h2></div><span class="district-total">{{ count($districts) }} districts</span></div>
            <label class="district-search-box"><i class="bi bi-search"></i><input type="search" data-filter-list=".district-list" data-empty-state="#district-empty" placeholder="Search by district or division" aria-label="Search districts"><kbd>/</kbd></label>
            <div class="stack gap-8 district-list">
                @foreach ($districts as $d)
                    @php $n = $districtProductCounts->get($d['name'], 0); @endphp
                    <a class="district-result" href="{{ route('products', ['district' => $d['name']]) }}">
                        <span class="district-result-pin"><i class="bi bi-geo-alt"></i></span>
                        <span class="district-result-copy"><b>{{ $d['name'] }}</b><small>{{ $d['division'] }} Division</small></span>
                        <span class="district-result-count {{ $n ? 'has-products' : '' }}">{{ $n }} <small>{{ $n === 1 ? 'product' : 'products' }}</small></span>
                        <i class="bi bi-chevron-right district-result-arrow"></i>
                    </a>
                @endforeach
            </div>
            <div class="district-no-results" id="district-empty" hidden><i class="bi bi-search"></i><b>No district found</b><span>Try another district or division name.</span></div>
        </section>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var districts = @json($districts);
    var map = L.map('district-map').setView([23.685, 90.356], 7);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 18, attribution: '&copy; OpenStreetMap contributors'}).addTo(map);
    districts.forEach(function (d) {
        L.circleMarker([d.lat, d.lng], {radius: 7, color: '#14532D', weight: 2, fillColor: '#1E7B34', fillOpacity: .85}).addTo(map)
            .bindPopup('<b>' + d.name + '</b><br>' + d.division + ' division<br><a href="{{ route('products') }}?district=' + encodeURIComponent(d.name) + '">See products</a>');
    });
</script>
@endpush
