<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class OrderService
{
    public const STEPS = ['pending' => 0, 'processing' => 1, 'shipped' => 2, 'delivered' => 3];
    public const STEP_LABELS = ['Order Placed', 'Processing', 'Shipped', 'Delivered'];

    /**
     * Create an order from cart lines. Prices and stock always come from the database, never from the browser.
     *
     * @param array $lines  [['id' => productId, 'qty' => int], ...]
     * @param array $d      name, phone, address, district (name), note, payment (cod|bkash|nagad|card), payment_reference
     */
    public static function place(int $userId, array $lines, array $d): array
    {
        if (! AccountVerification::isVerified($userId)) {
            throw new OrderException('Verify your account before placing an order.');
        }

        if (! in_array(($d['payment'] ?? null), ['cod', 'bkash', 'nagad', 'bank'], true)) {
            throw new OrderException('Choose a valid payment method.');
        }
        $qty = [];
        foreach ($lines as $l) {
            $id = (int) ($l['id'] ?? 0);
            $n = (int) ($l['qty'] ?? 0);
            if ($id < 1 || $n < 1) {
                throw new OrderException('The cart has an invalid item.');
            }
            $qty[$id] = ($qty[$id] ?? 0) + $n;
        }
        if (! $qty) {
            throw new OrderException('Your cart is empty.');
        }

        return DB::transaction(function () use ($userId, $qty, $d) {
            $products = DB::table('products as p')->join('farmers as f', 'f.id', '=', 'p.farmer_id')
                ->whereIn('p.id', array_keys($qty))->where('p.status', 'approved')->where('f.status', 'approved')
                ->lockForUpdate()->get(['p.*'])->keyBy('id');

            $subtotal = 0;
            foreach ($qty as $id => $n) {
                $p = $products[$id] ?? null;
                if (! $p) {
                    throw new OrderException('A product in your cart is no longer available. Please remove it and try again.');
                }
                if ($n > (int) $p->stock) {
                    throw new OrderException($p->name . ': only ' . $p->stock . ' left in stock.');
                }
                $subtotal += (int) $p->price * $n;
            }

            $delivery = Shop::deliveryCharge($subtotal);
            $total = $subtotal + $delivery;
            $district = DB::table('districts')->where('name', $d['district'])->first();
            $method = $d['payment'];
            $paid = false;
            $now = date('Y-m-d H:i:s');

            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $userId, 'order_no' => 'TMP' . bin2hex(random_bytes(6)), 'subtotal' => $subtotal, 'delivery_charge' => $delivery, 'total' => $total,
                'status' => 'pending', 'payment_method' => $method, 'payment_status' => $method === 'cod' ? 'unpaid' : 'pending',
                'name' => $d['name'], 'phone' => $d['phone'], 'address' => $d['address'], 'division_id' => $district?->division_id, 'district_id' => $district?->id,
                'latitude' => $district?->latitude, 'longitude' => $district?->longitude, 'note' => $d['note'] ?? null, 'created_at' => $now, 'updated_at' => $now,
            ]);
            $orderNo = 'FD' . (10000 + $orderId);
            DB::table('orders')->where('id', $orderId)->update(['order_no' => $orderNo]);

            foreach ($qty as $id => $n) {
                $p = $products[$id];
                DB::table('order_items')->insert([
                    'order_id' => $orderId, 'product_id' => $id, 'farmer_id' => $p->farmer_id, 'name' => $p->name, 'unit_price' => $p->price,
                    'quantity' => $n, 'created_at' => $now, 'updated_at' => $now,
                ]);
                DB::table('products')->where('id', $id)->decrement('stock', $n);
            }

            DB::table('payments')->insert([
                'order_id' => $orderId, 'method' => $method, 'amount' => $total, 'status' => 'pending',
                'reference' => $d['payment_reference'] ?? null, 'payer_account' => $d['payer_account'] ?? null,
                'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('tracking_events')->insert([
                'trackable_type' => 'order', 'trackable_id' => $orderId, 'stage' => 'Ordered', 'note' => 'Order placed by the customer',
                'location' => $district?->name, 'created_by' => $userId, 'created_at' => $now, 'updated_at' => $now,
            ]);
            FarmDirectNotifier::role('manager', 'New order placed', 'Order ' . $orderNo . ' is ready for processing.', '/manager/orders');

            return ['id' => $orderId, 'no' => $orderNo, 'subtotal' => $subtotal, 'delivery' => $delivery, 'total' => $total];
        });
    }

    private static function summary(object $o): array
    {
        $items = DB::table('order_items')->where('order_id', $o->id)->get(['name', 'quantity']);
        return [
            'id' => '#' . $o->order_no, 'no' => $o->order_no, 'date' => date('d M Y', strtotime($o->created_at)),
            'items' => $items->map(fn ($i) => $i->name . ' ×' . $i->quantity)->implode(', '),
            'total' => (int) $o->total, 'status' => ucfirst($o->status), 'key' => $o->status,
        ];
    }

    public static function forUser(int $userId, ?string $status = null): array
    {
        $q = DB::table('orders')->where('user_id', $userId)->orderByDesc('id');
        if ($status) {
            $q->where('status', strtolower($status));
        }
        return $q->get()->map(fn ($o) => self::summary($o))->all();
    }

    public static function stats(int $userId): array
    {
        $rows = DB::table('orders')->where('user_id', $userId)->get(['status', 'total']);
        return [
            'total' => $rows->count(),
            'pending' => $rows->where('status', 'pending')->count(),
            'delivered' => $rows->where('status', 'delivered')->count(),
            'spent' => (int) $rows->where('status', '!=', 'cancelled')->sum('total'),
        ];
    }

    /** One order with items, delivery man and payment: for the owner only */
    public static function find(string $orderNo, int $userId): ?array
    {
        $o = DB::table('orders')->where('order_no', $orderNo)->where('user_id', $userId)->first();
        if (! $o) {
            return null;
        }
        $items = DB::table('order_items as i')->join('products as p', 'p.id', '=', 'i.product_id')->join('farmers as f', 'f.id', '=', 'i.farmer_id')
            ->join('users as u', 'u.id', '=', 'f.user_id')->leftJoin('districts as d', 'd.id', '=', 'f.district_id')->where('i.order_id', $o->id)
            ->get(['i.product_id as product_id', 'i.name as name', 'i.unit_price as price', 'i.quantity as qty', 'p.unit as unit', 'p.image as image', 'p.emoji as emoji', 'u.name as farmer', 'd.name as district'])
            ->map(fn ($i) => ['product_id' => (int) $i->product_id, 'name' => $i->name, 'price' => (int) $i->price, 'qty' => (int) $i->qty, 'unit' => $i->unit, 'image' => $i->image, 'emoji' => $i->emoji ?: '📦', 'farmer' => $i->farmer, 'district' => $i->district])->all();
        $delivery = DB::table('deliveries as dl')->leftJoin('delivery_men as m', 'm.id', '=', 'dl.delivery_man_id')->where('dl.order_id', $o->id)->first(['m.name as man', 'm.phone as phone', 'dl.status as status']);
        $events = DB::table('tracking_events')->where('trackable_type', 'order')->where('trackable_id', $o->id)->orderBy('id')->get(['stage', 'note', 'created_at'])
            ->map(fn ($e) => ['stage' => $e->stage, 'note' => $e->note, 'at' => date('d M, g:i A', strtotime($e->created_at))])->all();

        return [
            'no' => $o->order_no, 'date' => date('d M Y', strtotime($o->created_at)), 'status' => ucfirst($o->status), 'key' => $o->status,
            'subtotal' => (int) $o->subtotal, 'delivery_charge' => (int) $o->delivery_charge, 'total' => (int) $o->total,
            'payment_method' => $o->payment_method, 'payment_status' => $o->payment_status,
            'name' => $o->name, 'phone' => $o->phone, 'address' => $o->address, 'note' => $o->note,
            'step' => self::STEPS[$o->status] ?? null, 'items' => $items, 'delivery' => $delivery ? ['man' => $delivery->man, 'phone' => $delivery->phone] : null, 'events' => $events,
        ];
    }

    /** The order to show on "Track Order" when none is chosen: the newest order that is not finished */
    public static function latestActiveNo(int $userId): ?string
    {
        return DB::table('orders')->where('user_id', $userId)->whereIn('status', ['pending', 'processing', 'shipped'])->orderByDesc('id')->value('order_no')
            ?? DB::table('orders')->where('user_id', $userId)->orderByDesc('id')->value('order_no');
    }

    /** Cancel an order that is still pending: the stock goes back to the shelf */
    public static function cancel(string $orderNo, int $userId): bool
    {
        return DB::transaction(function () use ($orderNo, $userId) {
            $o = DB::table('orders')->where('order_no', $orderNo)->where('user_id', $userId)->lockForUpdate()->first();
            if (! $o || $o->status !== 'pending') {
                return false;
            }
            self::cancelRow($o, $userId, 'Cancelled by the customer');
            return true;
        });
    }

    /** Shared by the customer and the manager: restock, refund marks, tracking event. Call inside a transaction. */
    public static function cancelRow(object $o, int $actorId, string $note): void
    {
        $now = date('Y-m-d H:i:s');
        foreach (DB::table('order_items')->where('order_id', $o->id)->get(['product_id', 'quantity']) as $i) {
            DB::table('products')->where('id', $i->product_id)->increment('stock', $i->quantity);
        }
        DB::table('orders')->where('id', $o->id)->update(['status' => 'cancelled', 'payment_status' => $o->payment_status === 'paid' ? 'refunded' : 'unpaid', 'updated_at' => $now]);
        DB::table('payments')->where('order_id', $o->id)->update(['status' => $o->payment_status === 'paid' ? 'refunded' : 'failed', 'updated_at' => $now]);
        DB::table('deliveries')->where('order_id', $o->id)->update(['status' => 'cancelled', 'updated_at' => $now]);
        DB::table('tracking_events')->insert(['trackable_type' => 'order', 'trackable_id' => $o->id, 'stage' => 'Cancelled', 'note' => $note, 'created_by' => $actorId, 'created_at' => $now, 'updated_at' => $now]);
    }
}
