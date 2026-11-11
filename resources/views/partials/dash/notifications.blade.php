@php
    $unread = collect($items->items())->where('unread', true)->count();
    $iconFor = function ($title) {
        foreach (['/order|delivery/i' => 'bag-check', '/stock/i' => 'exclamation-triangle', '/payment|paid/i' => 'cash-coin', '/product/i' => 'box-seam', '/backup/i' => 'hdd', '/message/i' => 'envelope', '/farmer|user/i' => 'person-plus', '/welcome/i' => 'stars'] as $re => $i) { if (preg_match($re, $title)) return $i; }
        return 'bell';
    };
@endphp
<div class="row">
    @include('partials.dash.tabs', ['tabs' => ['All', 'Unread (' . $unread . ')', 'Orders', 'System']])
    <form method="POST" action="{{ route('notifications.mark-all') }}">@csrf<button class="btn btn-outline btn-sm" type="submit"><i class="bi bi-check2-all"></i> Mark all as read</button></form>
</div>
<div class="card" data-notifs>
    @forelse ($items as $n)
        <div class="notif {{ $n['unread'] ? 'unread' : '' }}" data-cat="{{ preg_match('/order|delivery|payment|stock|product/i', $n['title']) ? 'orders' : 'system' }}">
            <span class="notif-ic" style="color:{{ $n['color'] }}"><i class="bi bi-{{ $iconFor($n['title']) }}"></i></span>
            <div class="body"><div class="bold">{{ $n['title'] }}</div><div class="muted">{{ $n['text'] }}</div>@if ($n['url'])<a class="green" href="{{ $n['url'] }}">Open</a>@endif</div>
            <span class="time"><i class="bi bi-clock"></i> {{ $n['time'] }}</span>
            @if ($n['unread'])<form method="POST" action="{{ route('notifications.read', $n['id']) }}">@csrf<button class="btn btn-outline btn-sm" type="submit">Mark read</button></form>@endif
        </div>
    @empty
        <p class="center muted" style="margin:20px 0 6px">Nothing here yet.</p>
    @endforelse
    <p class="center muted" data-empty hidden style="margin:20px 0 6px">Nothing here yet.</p>
</div>
{{ $items->links() }}
