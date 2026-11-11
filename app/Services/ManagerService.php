<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/** The manager's work: approving products, stock, orders, deliveries, warehouses and inventory reports. */
class ManagerService
{
    public const LOW_STOCK = 10;
    /** allowed order status changes */
    private const NEXT = ['pending' => ['processing', 'cancelled'], 'processing' => ['shipped', 'cancelled'], 'shipped' => ['delivered'], 'delivered' => [], 'cancelled' => []];

    private static function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    public static function stockLabel(int $stock): string
    {
        return $stock <= 0 ? 'Out of Stock' : ($stock < self::LOW_STOCK ? 'Low Stock' : 'In Stock');
    }

    // ---------- dashboard ----------
    public static function dashboard(): array
    {
        $days = [];
        foreach (range(9, 0) as $back) {
            $days[date('Y-m-d', strtotime("-$back day"))] = 0;
        }
        foreach (DB::table('orders')->where('status', '!=', 'cancelled')->where('created_at', '>=', date('Y-m-d', strtotime('-9 day')))->get(['total', 'created_at']) as $o) {
            $d = date('Y-m-d', strtotime($o->created_at));
            if (isset($days[$d])) {
                $days[$d] += (int) $o->total;
            }
        }

        return [
            'products' => DB::table('products')->where('status', 'approved')->count(),
            'orders' => DB::table('orders')->count(),
            'sales' => (int) DB::table('orders')->where('status', '!=', 'cancelled')->sum('total'),
            'to_assign' => DB::table('orders')->whereIn('status', ['pending', 'processing'])->whereNotIn('id', DB::table('deliveries')->select('order_id'))->count(),
            'waiting_approval' => DB::table('products')->where('status', 'pending')->count(),
            'recent' => array_slice(self::orders(), 0, 5), 'series' => array_values($days),
            'labels' => [date('j M', strtotime('-9 day')), date('j M', strtotime('-6 day')), date('j M', strtotime('-3 day')), date('j M')],
            'low' => array_slice(self::inventory(true), 0, 8),
        ];
    }

    // ---------- product approval ----------
    public static function approvals(): array
    {
        return DB::table('products as p')->join('farmers as f', 'f.id', '=', 'p.farmer_id')->join('users as u', 'u.id', '=', 'f.user_id')->join('categories as c', 'c.id', '=', 'p.category_id')
            ->orderByRaw("CASE p.status WHEN 'pending' THEN 0 WHEN 'rejected' THEN 1 ELSE 2 END")->orderByDesc('p.id')->limit(300)
            ->get(['p.id', 'p.name', 'p.image', 'p.emoji', 'p.price', 'p.unit', 'p.stock', 'p.status', 'p.rejection_reason', 'p.created_at', 'u.name as farmer', 'c.name as category'])
            ->map(fn ($p) => ['id' => (int) $p->id, 'name' => $p->name, 'image' => $p->image, 'emoji' => $p->emoji ?: '📦', 'farmer' => $p->farmer, 'category' => $p->category,
                'price' => '৳ ' . number_format($p->price) . ' / ' . $p->unit, 'stock' => (int) $p->stock, 'date' => date('d M Y', strtotime($p->created_at)), 'status' => ucfirst($p->status), 'reason' => $p->rejection_reason])->all();
    }

    public static function approve(int $productId, int $managerId): void
    {
        DB::transaction(function () use ($productId, $managerId) {
            $p = DB::table('products')->where('id', $productId)->lockForUpdate()->first() ?? throw new OrderException('Product not found.');
            if ($p->status === 'approved') {
                throw new OrderException($p->name . ' is already approved.');
            }
            $now = self::now();
            DB::table('products')->where('id', $productId)->update(['status' => 'approved', 'stage' => 'listed', 'rejection_reason' => null, 'updated_at' => $now]);
            foreach (['Approved' => 'Approved by the manager', 'Listed' => 'Now visible in the shop'] as $stage => $note) {
                DB::table('tracking_events')->insert(['trackable_type' => 'product', 'trackable_id' => $productId, 'stage' => $stage, 'note' => $note, 'created_by' => $managerId, 'created_at' => $now, 'updated_at' => $now]);
            }
            $farmerUserId = DB::table('farmers')->where('id', $p->farmer_id)->value('user_id');
            FarmDirectNotifier::user((int) $farmerUserId, 'Product approved', $p->name . ' is now listed in the shop.', '/farmer/products');
        });
    }

    public static function reject(int $productId, int $managerId, string $reason): void
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new OrderException('Please write a reason, so the farmer knows what to fix.');
        }
        DB::table('products')->where('id', $productId)->exists() || throw new OrderException('Product not found.');
        $now = self::now();
        DB::table('products')->where('id', $productId)->update(['status' => 'rejected', 'stage' => 'submitted', 'rejection_reason' => $reason, 'updated_at' => $now]);
        DB::table('tracking_events')->insert(['trackable_type' => 'product', 'trackable_id' => $productId, 'stage' => 'Rejected', 'note' => $reason, 'created_by' => $managerId, 'created_at' => $now, 'updated_at' => $now]);
        $farmerUserId = DB::table('farmers')->where('id', $p->farmer_id)->value('user_id');
        FarmDirectNotifier::user((int) $farmerUserId, 'Product needs changes', $p->name . ' was not approved: ' . $reason, '/farmer/products');
    }

    // ---------- inventory ----------
    public static function inventory(bool $lowOnly = false): array
    {
        $q = DB::table('products as p')->join('farmers as f', 'f.id', '=', 'p.farmer_id')->join('users as u', 'u.id', '=', 'f.user_id')->join('categories as c', 'c.id', '=', 'p.category_id')
            ->where('p.status', 'approved')->orderBy('p.stock')->orderBy('p.name');
        if ($lowOnly) {
            $q->where('p.stock', '<', self::LOW_STOCK);
        }
        $stored = DB::table('warehouse_stock')->selectRaw('product_id, SUM(quantity) as qty')->groupBy('product_id')->pluck('qty', 'product_id');

        return $q->get(['p.id', 'p.name', 'p.image', 'p.emoji', 'p.price', 'p.unit', 'p.stock', 'u.name as farmer', 'c.name as category'])
            ->map(fn ($p) => ['id' => (int) $p->id, 'name' => $p->name, 'image' => $p->image, 'emoji' => $p->emoji ?: '📦', 'category' => $p->category, 'farmer' => $p->farmer,
                'price' => '৳ ' . number_format($p->price) . ' / ' . $p->unit, 'stock' => (int) $p->stock, 'in_warehouses' => (int) ($stored[$p->id] ?? 0), 'status' => self::stockLabel((int) $p->stock)])->all();
    }

    public static function updateStock(int $productId, int $stock): void
    {
        DB::table('products')->where('id', $productId)->where('status', 'approved')->exists() || throw new OrderException('Product not found.');
        DB::table('products')->where('id', $productId)->update(['stock' => max(0, $stock), 'updated_at' => self::now()]);
    }

    /** The manager adds a product for a farmer; it is approved at once */
    public static function addProduct(int $managerId, int $farmerId, array $d): int
    {
        DB::table('farmers')->where('id', $farmerId)->where('status', 'approved')->exists() || throw new OrderException('Choose an approved farmer.');
        $id = FarmerService::createProduct($farmerId, $managerId, $d, null);
        self::approve($id, $managerId);
        return $id;
    }

    public static function farmerOptions(): array
    {
        return DB::table('farmers as f')->join('users as u', 'u.id', '=', 'f.user_id')->where('f.status', 'approved')->orderBy('u.name')->get(['f.id', 'u.name'])->map(fn ($r) => ['id' => (int) $r->id, 'name' => $r->name])->all();
    }

    // ---------- orders ----------
    public static function orders(?string $status = null): array
    {
        $q = DB::table('orders as o')->leftJoin('districts as d', 'd.id', '=', 'o.district_id')->leftJoin('deliveries as dl', 'dl.order_id', '=', 'o.id')->leftJoin('delivery_men as m', 'm.id', '=', 'dl.delivery_man_id')
            ->orderByDesc('o.id')->limit(300);
        if ($status) {
            $q->where('o.status', strtolower($status));
        }
        $rows = $q->get(['o.id', 'o.order_no', 'o.name', 'o.status', 'o.total', 'o.created_at', 'o.payment_method', 'o.payment_status', 'd.name as district', 'm.name as man']);
        $counts = DB::table('order_items')->whereIn('order_id', $rows->pluck('id')->all())->selectRaw('order_id, SUM(quantity) as n')->groupBy('order_id')->pluck('n', 'order_id');

        return $rows->map(fn ($o) => ['id' => '#' . $o->order_no, 'no' => $o->order_no, 'customer' => $o->name, 'district' => $o->district ?? '', 'items' => ($counts[$o->id] ?? 0) . ' items',
            'total' => (int) $o->total, 'delivery' => $o->man ?? 'Not assigned', 'status' => ucfirst($o->status), 'key' => $o->status, 'date' => date('d M Y', strtotime($o->created_at)),
            'payment' => $o->payment_method . ' · ' . $o->payment_status, 'next' => self::NEXT[$o->status] ?? []])->all();
    }

    public static function order(string $no): ?array
    {
        $o = DB::table('orders as o')->leftJoin('districts as d', 'd.id', '=', 'o.district_id')->where('o.order_no', $no)->first(['o.*', 'd.name as district']);
        if (! $o) {
            return null;
        }
        $items = DB::table('order_items as i')->join('farmers as f', 'f.id', '=', 'i.farmer_id')->join('users as u', 'u.id', '=', 'f.user_id')->leftJoin('districts as d', 'd.id', '=', 'f.district_id')
            ->where('i.order_id', $o->id)->get(['i.name', 'i.quantity', 'i.unit_price', 'u.name as farmer', 'd.name as farmer_district'])
            ->map(fn ($i) => ['name' => $i->name, 'qty' => (int) $i->quantity, 'price' => (int) $i->unit_price, 'farmer' => $i->farmer, 'district' => $i->farmer_district])->all();
        $dl = DB::table('deliveries as dl')->leftJoin('delivery_men as m', 'm.id', '=', 'dl.delivery_man_id')->where('dl.order_id', $o->id)->first(['m.name as man', 'm.phone as phone', 'dl.status as status']);
        $events = DB::table('tracking_events')->where('trackable_type', 'order')->where('trackable_id', $o->id)->orderBy('id')->get(['stage', 'note', 'created_at'])
            ->map(fn ($e) => ['stage' => $e->stage, 'note' => $e->note, 'at' => date('d M, g:i A', strtotime($e->created_at))])->all();

        return ['no' => $o->order_no, 'status' => ucfirst($o->status), 'key' => $o->status, 'date' => date('d M Y, g:i A', strtotime($o->created_at)), 'customer' => $o->name, 'phone' => $o->phone,
            'address' => $o->address, 'district' => $o->district, 'note' => $o->note, 'subtotal' => (int) $o->subtotal, 'delivery_charge' => (int) $o->delivery_charge, 'total' => (int) $o->total,
            'payment_method' => $o->payment_method, 'payment_status' => $o->payment_status, 'items' => $items, 'delivery' => $dl ? ['man' => $dl->man, 'phone' => $dl->phone, 'status' => $dl->status] : null,
            'events' => $events, 'next' => self::NEXT[$o->status] ?? []];
    }

    public static function setOrderStatus(string $no, string $to, int $managerId): void
    {
        DB::transaction(function () use ($no, $to, $managerId) {
            $o = DB::table('orders')->where('order_no', $no)->lockForUpdate()->first() ?? throw new OrderException('Order not found.');
            if (! in_array($to, self::NEXT[$o->status] ?? [], true)) {
                throw new OrderException('An order that is ' . $o->status . ' cannot be changed to ' . $to . '.');
            }
            $now = self::now();
            $dl = DB::table('deliveries')->where('order_id', $o->id)->first();

            if ($to === 'cancelled') {
                OrderService::cancelRow($o, $managerId, 'Cancelled by the manager');
                return;
            }
            if ($o->payment_method !== 'cod' && $o->payment_status !== 'paid') {
                throw new OrderException('Verify this online payment before processing or shipping the order.');
            }
            if ($to === 'shipped') {
                if (! $dl || ! $dl->delivery_man_id) {
                    throw new OrderException('Assign a delivery man before shipping this order.');
                }
                self::takeFromWarehouses($o->id);
                DB::table('deliveries')->where('id', $dl->id)->update(['status' => 'on_the_way', 'updated_at' => $now]);
            }
            if ($to === 'delivered') {
                DB::table('deliveries')->where('order_id', $o->id)->update(['status' => 'delivered', 'delivered_at' => $now, 'updated_at' => $now]);
                if ($o->payment_status !== 'paid') {
                    DB::table('orders')->where('id', $o->id)->update(['payment_status' => 'paid']);
                    DB::table('payments')->where('order_id', $o->id)->update(['status' => 'completed', 'updated_at' => $now]);
                }
            }
            DB::table('orders')->where('id', $o->id)->update(['status' => $to, 'updated_at' => $now]);
            DB::table('tracking_events')->insert(['trackable_type' => 'order', 'trackable_id' => $o->id, 'stage' => ucfirst($to), 'note' => 'Updated by the manager', 'created_by' => $managerId, 'created_at' => $now, 'updated_at' => $now]);
            FarmDirectNotifier::user((int) $o->user_id, 'Order ' . $to, 'Your order ' . $no . ' is now ' . $to . '.', '/client/orders');
        });
    }

    /** When an order leaves for delivery, its items are taken from the warehouses that hold the most of each product */
    private static function takeFromWarehouses(int $orderId): void
    {
        foreach (DB::table('order_items')->where('order_id', $orderId)->get(['product_id', 'quantity']) as $i) {
            $need = (int) $i->quantity;
            $rows = DB::table('warehouse_stock')->where('product_id', $i->product_id)->where('quantity', '>', 0)
                ->orderByDesc('quantity')->lockForUpdate()->get();
            if ($rows->sum('quantity') < $need) {
                throw new OrderException('Insufficient warehouse stock to ship product ' . $i->product_id . '.');
            }
            foreach ($rows as $w) {
                if ($need <= 0) {
                    break;
                }
                $take = min($need, (int) $w->quantity);
                DB::table('warehouse_stock')->where('id', $w->id)->decrement('quantity', $take, ['updated_at' => self::now()]);
                $need -= $take;
            }
        }
    }

    // ---------- deliveries ----------
    public static function deliveries(): array
    {
        $waiting = DB::table('orders as o')->leftJoin('districts as d', 'd.id', '=', 'o.district_id')->whereIn('o.status', ['pending', 'processing'])
            ->whereNotIn('o.id', DB::table('deliveries')->select('order_id'))->orderBy('o.id')
            ->get(['o.order_no', 'o.name', 'o.total', 'o.address', 'd.name as district'])
            ->map(fn ($o) => ['no' => $o->order_no, 'id' => '#' . $o->order_no, 'customer' => $o->name, 'district' => $o->district ?? '', 'address' => $o->address, 'total' => (int) $o->total])->all();
        $active = DB::table('deliveries')->whereIn('status', ['assigned', 'on_the_way'])->selectRaw('delivery_man_id, COUNT(*) as n')->groupBy('delivery_man_id')->pluck('n', 'delivery_man_id');
        $team = DB::table('delivery_men')->orderBy('name')->get()->map(fn ($m) => ['id' => (int) $m->id, 'name' => $m->name, 'phone' => $m->phone, 'active' => (int) ($active[$m->id] ?? 0), 'busy' => ($active[$m->id] ?? 0) > 0])->all();

        return ['waiting' => $waiting, 'team' => $team, 'in_transit' => DB::table('deliveries')->where('status', 'on_the_way')->count(),
            'delivered_today' => DB::table('deliveries')->where('status', 'delivered')->where('delivered_at', '>=', date('Y-m-d'))->count()];
    }

    public static function assign(string $no, int $manId, int $managerId): void
    {
        DB::transaction(function () use ($no, $manId, $managerId) {
            $o = DB::table('orders')->where('order_no', $no)->lockForUpdate()->first() ?? throw new OrderException('Order not found.');
            DB::table('delivery_men')->where('id', $manId)->exists() || throw new OrderException('Choose a delivery man.');
            if (! in_array($o->status, ['pending', 'processing'], true)) {
                throw new OrderException('Only pending or processing orders can be assigned.');
            }
            $now = self::now();
            $existing = DB::table('deliveries')->where('order_id', $o->id)->first();
            if ($existing) {
                DB::table('deliveries')->where('id', $existing->id)->update(['delivery_man_id' => $manId, 'updated_at' => $now]);
            } else {
                DB::table('deliveries')->insert(['order_id' => $o->id, 'delivery_man_id' => $manId, 'status' => 'assigned', 'assigned_at' => $now, 'created_at' => $now, 'updated_at' => $now]);
            }
            if ($o->status === 'pending') {
                DB::table('orders')->where('id', $o->id)->update(['status' => 'processing', 'updated_at' => $now]);
            }
            DB::table('tracking_events')->insert(['trackable_type' => 'order', 'trackable_id' => $o->id, 'stage' => 'Processing', 'note' => 'Delivery man assigned', 'created_by' => $managerId, 'created_at' => $now, 'updated_at' => $now]);
        });
    }

    public static function addDeliveryMan(string $name, ?string $phone): void
    {
        $now = self::now();
        DB::table('delivery_men')->insert(['name' => $name, 'phone' => $phone, 'is_available' => true, 'created_at' => $now, 'updated_at' => $now]);
    }

    // ---------- warehouses ----------
    public static function warehouses(): array
    {
        $used = DB::table('warehouse_stock')->selectRaw('warehouse_id, SUM(quantity) as qty, COUNT(*) as lines')->groupBy('warehouse_id')->get()->keyBy('warehouse_id');

        return DB::table('warehouses as w')->leftJoin('districts as d', 'd.id', '=', 'w.district_id')->orderBy('w.id')->get(['w.id', 'w.name', 'w.capacity', 'd.name as district'])
            ->map(function ($w) use ($used) {
                $q = (int) ($used[$w->id]->qty ?? 0);
                return ['id' => (int) $w->id, 'name' => $w->name, 'district' => $w->district ?? '', 'capacity' => (int) $w->capacity, 'used' => $q, 'pct' => $w->capacity ? min(100, (int) round($q / $w->capacity * 100)) : 0, 'lines' => (int) ($used[$w->id]->lines ?? 0)];
            })->all();
    }

    public static function warehouseStock(): array
    {
        return DB::table('warehouse_stock as s')->join('warehouses as w', 'w.id', '=', 's.warehouse_id')->join('products as p', 'p.id', '=', 's.product_id')->join('categories as c', 'c.id', '=', 'p.category_id')
            ->orderBy('w.name')->orderBy('p.name')->get(['s.id', 's.warehouse_id', 's.product_id', 's.quantity', 's.reorder_level', 'w.name as warehouse', 'p.name as product', 'p.image', 'p.emoji', 'p.unit', 'c.name as category'])
            ->map(fn ($r) => ['product_id' => (int) $r->product_id, 'warehouse_id' => (int) $r->warehouse_id, 'product' => $r->product, 'image' => $r->image, 'emoji' => $r->emoji ?: '📦', 'category' => $r->category, 'warehouse' => $r->warehouse,
                'qty' => (int) $r->quantity, 'unit' => $r->unit, 'status' => $r->quantity <= 0 ? 'Out of Stock' : ($r->quantity < $r->reorder_level ? 'Low Stock' : 'In Stock')])->all();
    }

    public static function receiveStock(int $warehouseId, int $productId, int $qty, int $managerId): void
    {
        if ($qty < 1) {
            throw new OrderException('Enter a quantity of 1 or more.');
        }
        DB::transaction(function () use ($warehouseId, $productId, $qty, $managerId) {
            DB::table('warehouses')->where('id', $warehouseId)->exists() || throw new OrderException('Choose a warehouse.');
            DB::table('products')->where('id', $productId)->where('status', 'approved')->exists() || throw new OrderException('Choose an approved product.');
            $now = self::now();
            $row = DB::table('warehouse_stock')->where('warehouse_id', $warehouseId)->where('product_id', $productId)->first();
            if ($row) {
                DB::table('warehouse_stock')->where('id', $row->id)->update(['quantity' => $row->quantity + $qty, 'updated_at' => $now]);
            } else {
                DB::table('warehouse_stock')->insert(['warehouse_id' => $warehouseId, 'product_id' => $productId, 'quantity' => $qty, 'reorder_level' => self::LOW_STOCK, 'created_at' => $now, 'updated_at' => $now]);
            }
            DB::table('products')->where('id', $productId)->increment('stock', $qty);
            DB::table('tracking_events')->insert(['trackable_type' => 'product', 'trackable_id' => $productId, 'stage' => 'In Warehouse', 'note' => $qty . ' received', 'created_by' => $managerId, 'created_at' => $now, 'updated_at' => $now]);
        });
    }

    public static function transferStock(int $fromId, int $toId, int $productId, int $qty): void
    {
        if ($fromId === $toId) {
            throw new OrderException('Choose two different warehouses.');
        }
        if ($qty < 1) {
            throw new OrderException('Enter a quantity of 1 or more.');
        }
        DB::transaction(function () use ($fromId, $toId, $productId, $qty) {
            $from = DB::table('warehouse_stock')->where('warehouse_id', $fromId)->where('product_id', $productId)->lockForUpdate()->first();
            if (! $from || $from->quantity < $qty) {
                throw new OrderException('That warehouse does not have ' . $qty . ' of this product.');
            }
            DB::table('warehouses')->where('id', $toId)->exists() || throw new OrderException('Choose a warehouse to move to.');
            $now = self::now();
            DB::table('warehouse_stock')->where('id', $from->id)->update(['quantity' => $from->quantity - $qty, 'updated_at' => $now]);
            $to = DB::table('warehouse_stock')->where('warehouse_id', $toId)->where('product_id', $productId)->first();
            if ($to) {
                DB::table('warehouse_stock')->where('id', $to->id)->update(['quantity' => $to->quantity + $qty, 'updated_at' => $now]);
            } else {
                DB::table('warehouse_stock')->insert(['warehouse_id' => $toId, 'product_id' => $productId, 'quantity' => $qty, 'reorder_level' => $from->reorder_level, 'created_at' => $now, 'updated_at' => $now]);
            }
        });
    }

    public static function addWarehouse(string $name, string $district, int $capacity): void
    {
        $d = DB::table('districts')->where('name', $district)->first() ?? throw new OrderException('Choose a valid district.');
        $now = self::now();
        DB::table('warehouses')->insert(['name' => $name, 'district_id' => $d->id, 'capacity' => max(1, $capacity), 'created_at' => $now, 'updated_at' => $now]);
    }

    // ---------- reports ----------
    public static function report(): array
    {
        $inv = self::inventory();
        $byCat = [];
        foreach ($inv as $p) {
            $byCat[$p['category']] = ($byCat[$p['category']] ?? 0) + $p['stock'];
        }
        $value = (int) DB::table('products')->where('status', 'approved')->selectRaw('SUM(price * stock) as v')->value('v');
        $status = ['In Stock' => 0, 'Low Stock' => 0, 'Out of Stock' => 0];
        foreach ($inv as $p) {
            $status[$p['status']]++;
        }

        return ['value' => $value, 'products' => count($inv), 'low' => $status['Low Stock'], 'out' => $status['Out of Stock'], 'status' => $status,
            'by_category' => array_map(null, array_keys($byCat), array_values($byCat)), 'low_items' => array_values(array_filter($inv, fn ($p) => $p['status'] !== 'In Stock'))];
    }

    /** Rows for the CSV download */
    public static function inventoryCsv(): array
    {
        return array_merge([['Product', 'Category', 'Farmer', 'Price', 'Stock', 'In warehouses', 'Status']],
            array_map(fn ($p) => [$p['name'], $p['category'], $p['farmer'], $p['price'], $p['stock'], $p['in_warehouses'], $p['status']], self::inventory()));
    }
}
