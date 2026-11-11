@php
    $D = \App\Support\DemoData::class;
    $days = ['1 Oct', '2 Oct', '3 Oct', '4 Oct', '5 Oct', '6 Oct', '7 Oct', '8 Oct', '9 Oct', '10 Oct'];
    $data = [
        'sales' => ['headers' => ['Day', 'Sales (BDT)'], 'rows' => array_map(fn ($d, $v) => [$d, $v], $days, $D::sales())],
        'orders' => ['headers' => ['Order', 'Customer', 'District', 'Items', 'Total (BDT)', 'Status'], 'rows' => array_map(fn ($o) => [$o['id'], $o['customer'], $o['area'], $o['items'], $o['total'], $o['status']], $D::staffOrders())],
        'users' => ['headers' => ['Name', 'Email', 'Role', 'Joined', 'Status'], 'rows' => array_map(fn ($u) => [$u['name'], $u['email'], $u['role'], $u['joined'], $u['status']], $D::users())],
        'inventory' => ['headers' => ['Product', 'Category', 'Price (BDT)', 'Unit', 'Stock', 'Farmer', 'District'], 'rows' => array_map(fn ($p) => [$p['name'], $p['category'], $p['price'], $p['unit'], $p['stock'], $p['farmer'], $p['district']], $D::products())],
        'earnings' => ['headers' => ['Farmer', 'District', 'Rating', 'Reviews'], 'rows' => array_map(fn ($f) => [$f['name'], $f['district'], $f['rating'], $f['reviews']], $D::farmers())],
        'delivery' => ['headers' => ['Order', 'Customer', 'District', 'Delivery Man', 'Status'], 'rows' => array_map(fn ($o) => [$o['id'], $o['customer'], $o['area'], $o['delivery'], $o['status']], $D::staffOrders())],
        'transactions' => ['headers' => ['Transaction', 'Order', 'Customer', 'Method', 'Amount (BDT)', 'Date', 'Status'], 'rows' => array_map(fn ($t) => [$t['id'], $t['order'], $t['customer'], $t['method'], $t['amount'], $t['date'], $t['status']], $D::transactions())],
    ];
@endphp
<script>window.FD_DATA = @json($data);</script>
