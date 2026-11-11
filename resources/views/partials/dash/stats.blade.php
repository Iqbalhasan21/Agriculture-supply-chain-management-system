@php
    $map = ['/order/i' => 'bag-check', '/user|team/i' => 'people', '/farmer/i' => 'person-badge', '/product/i' => 'box-seam', '/revenue|sales|earn|spent|value|payout|fee/i' => 'cash-coin',
        '/deliver|transit|assign/i' => 'truck', '/stock/i' => 'boxes', '/pending|waiting/i' => 'hourglass-split', '/backup|size|stored/i' => 'hdd', '/schedule/i' => 'calendar3',
        '/complet|fail|transaction/i' => 'credit-card'];
    $iconFor = function ($label) use ($map) { foreach ($map as $re => $i) { if (preg_match($re, $label)) return $i; } return 'graph-up'; };
@endphp
<div class="stats">
    @foreach ($stats as [$label, $value])
        <div class="card stat">
            <div class="row" style="flex-wrap:nowrap"><div class="label">{{ $label }}</div><span class="stat-ic"><i class="bi bi-{{ $iconFor($label) }}"></i></span></div>
            <div class="value">{{ $value }}</div>
        </div>
    @endforeach
</div>
