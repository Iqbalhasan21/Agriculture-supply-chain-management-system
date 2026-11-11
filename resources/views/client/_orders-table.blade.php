<div class="table-wrap">
    <table data-real>
        <thead><tr><th>Order ID</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        @foreach ($orders as $o)
            <tr>
                <td>{{ $o['id'] }}</td><td>{{ $o['date'] }}</td><td>{{ $o['items'] }}</td><td>৳ {{ number_format($o['total']) }}</td>
                <td>@include('partials.status-badge', ['status' => $o['status']])</td>
                <td class="actions">
                    <a href="{{ route('client.track', $o['no']) }}"><i class="bi bi-geo-alt"></i> {{ in_array($o['status'], ['Shipped', 'Processing']) ? 'Track' : 'View' }}</a>
                    @if ($o['status'] === 'Pending')
                        <form method="POST" action="{{ route('client.orders.cancel', $o['no']) }}" style="display:inline" onsubmit="return confirm('Cancel order {{ $o['id'] }}?')">@csrf<button type="submit" class="link-btn" style="color:var(--r)"><i class="bi bi-x-circle"></i> Cancel</button></form>
                    @endif
                </td>
            </tr>
        @endforeach
        @if (! count($orders))<tr><td colspan="6" class="center muted" style="padding:30px">No orders yet. <a class="green bold" href="{{ route('products') }}">Start shopping</a></td></tr>@endif
        </tbody>
    </table>
</div>
