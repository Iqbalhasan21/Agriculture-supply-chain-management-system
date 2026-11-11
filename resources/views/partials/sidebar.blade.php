@php
    $icons = ['Dashboard' => 'speedometer2', 'Profile' => 'person-circle', 'My Orders' => 'bag-check', 'Orders' => 'bag-check', 'My Cart' => 'cart3', 'Track Order' => 'geo-alt',
        'Write a Review' => 'star', 'Notifications' => 'bell', 'Logout' => 'box-arrow-right', 'My Products' => 'basket3', 'Add Product' => 'plus-circle', 'Product Tracking' => 'truck',
        'Farm Location' => 'pin-map', 'Earnings' => 'cash-coin', 'Inventory' => 'boxes', 'Deliveries' => 'truck', 'Warehouse' => 'building', 'Approve Products' => 'clipboard-check',
        'Reports' => 'bar-chart-line', 'Users' => 'people', 'Roles & Permissions' => 'shield-lock', 'Categories' => 'tags', 'Transactions' => 'credit-card', 'Messages' => 'envelope', 'Chats' => 'chat-dots',
        'Backup & Restore' => 'hdd', 'System Settings' => 'gear'];
@endphp
<aside class="sidebar">
    @include('partials.logo')
    @php($unreadCount = auth()->user()->unreadNotifications()->count())
    @foreach (\App\Support\DemoData::menu($role) as [$label, $name])
        @if ($label === 'Logout')
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="item"><i class="bi bi-box-arrow-right"></i>Logout</button></form>
        @else
            <a href="{{ route($name) }}" class="item {{ request()->routeIs($name) ? 'active' : '' }}"><i class="bi bi-{{ $icons[$label] ?? 'circle' }}"></i>{{ $label }}
                @if ($label === 'Notifications')<span class="nav-badge" @if ($unreadCount === 0) hidden @endif>{{ $unreadCount }}</span>@endif
            </a>
        @endif
    @endforeach
</aside>
