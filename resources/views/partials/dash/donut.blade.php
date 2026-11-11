@php
    $total = array_sum(array_column($segments, 1)); $acc = 0; $stops = [];
    foreach ($segments as [$l, $v, $c]) { $from = $acc / $total * 100; $acc += $v; $to = $acc / $total * 100; $stops[] = "$c {$from}% {$to}%"; }
@endphp
<div class="row gap-20" style="justify-content:flex-start;flex-wrap:nowrap">
    <div class="donut" role="img" aria-label="{{ $label ?? 'Chart' }}" style="background:conic-gradient({{ implode(',', $stops) }})"></div>
    <div class="legend">@foreach ($segments as [$l, $v, $c])<span><i style="background:{{ $c }}"></i>{{ $l }} ({{ $v }})</span>@endforeach</div>
</div>
