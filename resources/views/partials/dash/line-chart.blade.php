@php
    $w = 600; $h = 190; $color = $color ?? '#1E7B34';
    $max = max($points) * 1.1; $n = count($points); $pts = [];
    foreach ($points as $i => $v) { $pts[] = round($i * $w / ($n - 1), 1) . ',' . round($h - ($v / $max) * $h, 1); }
    $line = implode(' ', $pts);
@endphp
<svg class="line-chart" viewBox="0 0 {{ $w }} {{ $h }}" preserveAspectRatio="none" role="img" aria-label="{{ $label ?? 'Sales over time' }}">
    @for ($i = 0; $i < 5; $i++)<line class="grid-line" x1="0" x2="{{ $w }}" y1="{{ $i * ($h - 1) / 4 }}" y2="{{ $i * ($h - 1) / 4 }}"/>@endfor
    <polygon points="0,{{ $h }} {{ $line }} {{ $w }},{{ $h }}" fill="{{ $color }}" opacity=".13"/>
    <polyline points="{{ $line }}" fill="none" stroke="{{ $color }}" stroke-width="3" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
</svg>
<div class="row muted" style="font-size:11px">@foreach ($labels ?? ['1 Oct', '4 Oct', '7 Oct', '10 Oct'] as $l)<span>{{ $l }}</span>@endforeach</div>
