@php
    $iconFor = function ($label) {
        foreach (['/add|\+/i' => 'plus-lg', '/export|download/i' => 'download', '/filter/i' => 'funnel', '/transfer/i' => 'arrow-left-right', '/update/i' => 'arrow-repeat', '/approve/i' => 'check-lg', '/assign/i' => 'person-plus'] as $re => $i) { if (preg_match($re, $label)) return $i; }
        return null;
    };
@endphp
<div class="toolbar">
    <div class="input-icon" style="flex:1;max-width:320px"><i class="bi bi-search"></i><input type="search" data-live-search placeholder="{{ $placeholder ?? 'Search...' }}" aria-label="Search"></div>
    <div class="row gap-12">
        @foreach ($buttons ?? [] as $b)
            @php $label = $b[0]; $kind = $b[1]; $do = $b[2] ?? null; $ic = $iconFor($label); @endphp
            <button type="button" class="btn {{ $kind === 'outline' ? 'btn-outline' : '' }} {{ $do ? '' : 'demo-action' }}" @if ($do) data-do="{{ $do }}" @else data-toast="{{ ltrim($label, '+ ') }} (demo)" @endif>@if ($ic)<i class="bi bi-{{ $ic }}"></i>@endif {{ ltrim($label, '+ ') }}</button>
        @endforeach
    </div>
</div>
