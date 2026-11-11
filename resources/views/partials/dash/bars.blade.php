@php $max = max(array_column($data, 1)); $color = $color ?? '#1E7B34'; @endphp
<div class="bars" role="img" aria-label="{{ $label ?? 'Bar chart' }}">
    @foreach ($data as [$l, $v])
        <div class="col"><b>{{ $v }}</b><i style="height:{{ max(4, round($v / $max * 150)) }}px;background:{{ $color }}"></i><span>{{ $l }}</span></div>
    @endforeach
</div>
