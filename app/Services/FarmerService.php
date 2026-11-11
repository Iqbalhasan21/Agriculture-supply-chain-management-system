<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/** Everything a farmer sees: own products, orders for own products, tracking, earnings and farm location. */
class FarmerService
{
    public const STAGES = ['Submitted', 'Approved', 'In Warehouse', 'Listed', 'Ordered', 'Processing', 'Shipped', 'Delivered'];
    private const PRODUCT_STAGE = ['submitted' => 0, 'approved' => 1, 'in_warehouse' => 2, 'listed' => 3];
    private const ORDER_STAGE = ['pending' => 4, 'processing' => 5, 'shipped' => 6, 'delivered' => 7];

    /** Farmer record id of this (approved) farmer user */
    public static function farmerId(int $userId): ?int
    {
        $id = DB::table('farmers')->where('user_id', $userId)->value('id');
        return $id ? (int) $id : null;
    }

    public static function feePercent(): int
    {
        return (int) Shop::setting('platform_fee_percent', 10);
    }

    private static function items(int $fid)
    {
        return DB::table('order_items as i')->join('orders as o', 'o.id', '=', 'i.order_id')->where('i.farmer_id', $fid);
    }

    // ---------- money ----------
    public static function earnings(int $fid): array
    {
        $rows = self::items($fid)->where('o.status', '!=', 'cancelled')->get(['i.unit_price', 'i.quantity', 'o.status', 'o.created_at', 'o.order_no', 'o.id as order_id', 'i.name']);
        $fee = self::feePercent();
        $sales = $pendingSales = 0;
        $months = [];
        foreach (range(5, 0) as $back) {
            $months[date('Y-m', strtotime("first day of -$back month"))] = 0;
        }
        foreach ($rows as $r) {
            $amount = (int) $r->unit_price * (int) $r->quantity;
            $sales += $amount;
            if ($r->status !== 'delivered') {
                $pendingSales += $amount;
            }
            $m = date('Y-m', strtotime($r->created_at));
            if (isset($months[$m])) {
                $months[$m] += $amount;
            }
        }
        $platformFee = (int) round($sales * $fee / 100);

        return [
            'sales' => $sales, 'fee_percent' => $fee, 'fee' => $platformFee, 'net' => $sales - $platformFee,
            'pending_payout' => $pendingSales - (int) round($pendingSales * $fee / 100),
            'months' => array_map(null, array_map(fn ($k) => date('M', strtotime($k . '-01')), array_keys($months)), array_values($months)),
            'payments' => $rows->sortByDesc('order_id')->take(20)->map(fn ($r) => [
                'date' => date('d M Y', strtotime($r->created_at)), 'order' => '#' . $r->order_no, 'items' => $r->name . ' × ' . $r->quantity,
                'sales' => (int) $r->unit_price * (int) $r->quantity, 'fee' => (int) round((int) $r->unit_price * (int) $r->quantity * $fee / 100),
                'status' => $r->status === 'delivered' ? 'Completed' : 'Pending',
            ])->values()->all(),
        ];
    }

    // ---------- dashboard ----------
    public static function dashboard(int $fid): array
    {
        $e = self::earnings($fid);
        $orders = self::orders($fid);
        $days = [];
        foreach (range(9, 0) as $back) {
            $days[date('Y-m-d', strtotime("-$back day"))] = 0;
        }
        foreach (self::items($fid)->where('o.status', '!=', 'cancelled')->get(['i.unit_price', 'i.quantity', 'o.created_at']) as $r) {
            $d = date('Y-m-d', strtotime($r->created_at));
            if (isset($days[$d])) {
                $days[$d] += (int) $r->unit_price * (int) $r->quantity;
            }
        }

        return [
            'products' => DB::table('products')->where('farmer_id', $fid)->count(),
            'orders' => count($orders), 'sales' => $e['sales'], 'net' => $e['net'],
            'recent' => array_slice($orders, 0, 5), 'series' => array_values($days),
            'labels' => [date('j M', strtotime('-9 day')), date('j M', strtotime('-6 day')), date('j M', strtotime('-3 day')), date('j M')],
            'product_cards' => array_slice(self::products($fid), 0, 5),
        ];
    }

    // ---------- products ----------
    public static function products(int $fid): array
    {
        return DB::table('products as p')->join('categories as c', 'c.id', '=', 'p.category_id')->where('p.farmer_id', $fid)->orderByDesc('p.id')
            ->get(['p.id', 'p.name', 'p.image', 'p.emoji', 'p.unit', 'p.price', 'p.stock', 'p.status', 'p.stage', 'p.rejection_reason', 'c.name as category'])
            ->map(fn ($p) => ['id' => (int) $p->id, 'name' => $p->name, 'image' => $p->image, 'emoji' => $p->emoji ?: '📦', 'category' => $p->category, 'unit' => $p->unit, 'price' => (int) $p->price,
                'stock' => (int) $p->stock, 'status' => ucfirst($p->status), 'stage' => ucwords(str_replace('_', ' ', $p->stage)), 'reason' => $p->rejection_reason])->all();
    }

    public static function product(int $fid, int $id): ?array
    {
        $p = DB::table('products as p')->join('categories as c', 'c.id', '=', 'p.category_id')->where('p.farmer_id', $fid)->where('p.id', $id)
            ->first(['p.*', 'c.name as category']);
        return $p ? (array) $p : null;
    }

    private static function categoryId(string $name): int
    {
        $id = DB::table('categories')->where('name', $name)->value('id');
        if (! $id) {
            throw new OrderException('Please choose a valid category.');
        }
        return (int) $id;
    }

    public static function createProduct(int $fid, int $userId, array $d, ?string $image): int
    {
        $now = date('Y-m-d H:i:s');
        $id = DB::table('products')->insertGetId([
            'farmer_id' => $fid, 'category_id' => self::categoryId($d['category']), 'name' => $d['name'], 'unit' => $d['unit'], 'price' => (int) $d['price'],
            'stock' => (int) $d['stock'], 'description' => $d['description'] ?? null, 'image' => $image, 'harvest_date' => $d['harvest_date'] ?: null,
            'status' => 'pending', 'stage' => 'submitted', 'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('tracking_events')->insert(['trackable_type' => 'product', 'trackable_id' => $id, 'stage' => 'Submitted', 'note' => 'Submitted for approval', 'created_by' => $userId, 'created_at' => $now, 'updated_at' => $now]);
        return $id;
    }

    /** Changing the name, category or unit of a live product sends it back to the manager for approval */
    public static function updateProduct(int $fid, int $userId, int $id, array $d, ?string $newImage): bool
    {
        $p = self::product($fid, $id) ?? throw new OrderException('Product not found.');
        $catId = self::categoryId($d['category']);
        $needsReview = $p['status'] === 'approved' && ($p['name'] !== $d['name'] || (int) $p['category_id'] !== $catId || $p['unit'] !== $d['unit'] || $newImage);
        $now = date('Y-m-d H:i:s');
        $up = ['category_id' => $catId, 'name' => $d['name'], 'unit' => $d['unit'], 'price' => (int) $d['price'], 'stock' => (int) $d['stock'],
            'description' => $d['description'] ?? null, 'harvest_date' => $d['harvest_date'] ?: null, 'updated_at' => $now];
        if ($newImage) {
            $up['image'] = $newImage;
        }
        if ($p['status'] === 'rejected' || $needsReview) {
            $up += ['status' => 'pending', 'stage' => 'submitted', 'rejection_reason' => null];
            DB::table('tracking_events')->insert(['trackable_type' => 'product', 'trackable_id' => $id, 'stage' => 'Submitted', 'note' => 'Changed and sent for approval again', 'created_by' => $userId, 'created_at' => $now, 'updated_at' => $now]);
        }
        DB::table('products')->where('id', $id)->update($up);
        return $needsReview || $p['status'] === 'rejected';
    }

    public static function updateStock(int $fid, int $id, int $stock): void
    {
        self::product($fid, $id) ?? throw new OrderException('Product not found.');
        DB::table('products')->where('id', $id)->update(['stock' => max(0, $stock), 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public static function deleteProduct(int $fid, int $id): void
    {
        self::product($fid, $id) ?? throw new OrderException('Product not found.');
        if (DB::table('order_items')->where('product_id', $id)->exists()) {
            throw new OrderException('This product already has orders, so it cannot be deleted. Set its stock to 0 instead.');
        }
        DB::table('products')->where('id', $id)->delete();
    }

    // ---------- orders ----------
    /** Orders that contain this farmer's products. Amounts count only this farmer's items. */
    public static function orders(int $fid, ?string $status = null): array
    {
        $q = self::items($fid)->leftJoin('districts as d', 'd.id', '=', 'o.district_id')->orderByDesc('o.id');
        if ($status) {
            $q->where('o.status', strtolower($status));
        }
        $rows = $q->get(['o.id as order_id', 'o.order_no', 'o.name as customer', 'o.status', 'o.created_at', 'd.name as district', 'i.name', 'i.quantity', 'i.unit_price']);
        $out = [];
        foreach ($rows as $r) {
            $o = &$out[$r->order_id];
            $o ??= ['no' => $r->order_no, 'id' => '#' . $r->order_no, 'customer' => $r->customer, 'district' => $r->district ?? '', 'date' => date('d M Y', strtotime($r->created_at)),
                'status' => ucfirst($r->status), 'key' => $r->status, 'items' => [], 'amount' => 0];
            $o['items'][] = $r->name . ' × ' . $r->quantity;
            $o['amount'] += (int) $r->unit_price * (int) $r->quantity;
            unset($o);
        }
        return array_values(array_map(fn ($o) => ['items' => implode(', ', $o['items'])] + $o, $out));
    }

    // ---------- tracking ----------
    /** The journey of one product: farm -> approval -> warehouse -> listed -> ordered -> delivered */
    public static function journey(int $fid, int $productId): ?array
    {
        $p = self::product($fid, $productId);
        if (! $p) {
            return null;
        }
        $idx = self::PRODUCT_STAGE[$p['stage']] ?? 0;
        $latest = self::items($fid)->where('i.product_id', $productId)->where('o.status', '!=', 'cancelled')->orderByDesc('o.id')->first(['o.status', 'o.order_no', 'i.quantity']);
        $orderIdx = $latest ? (self::ORDER_STAGE[$latest->status] ?? null) : null;
        $cur = $orderIdx ?? $idx;
        $events = DB::table('tracking_events')->where('trackable_type', 'product')->where('trackable_id', $productId)->orderBy('id')->get(['stage', 'note', 'created_at'])->keyBy('stage');

        $steps = [];
        foreach (self::STAGES as $i => $label) {
            $e = $events[$label] ?? null;
            $steps[] = ['label' => $label, 'state' => $i < $cur ? 'done' : ($i === $cur ? 'current' : ''), 'note' => $e?->note, 'at' => $e ? date('d M, g:i A', strtotime($e->created_at)) : null];
        }
        return ['product' => $p, 'steps' => $steps, 'rejected' => $p['status'] === 'rejected', 'order' => $latest ? ['no' => $latest->order_no, 'qty' => (int) $latest->quantity] : null];
    }

    // ---------- location and profile ----------
    public static function farm(int $userId): ?array
    {
        $f = DB::table('farmers as f')->join('users as u', 'u.id', '=', 'f.user_id')->leftJoin('districts as d', 'd.id', '=', 'f.district_id')->where('f.user_id', $userId)
            ->first(['f.*', 'u.name', 'u.phone', 'u.email', 'u.photo', 'd.name as district']);
        return $f ? (array) $f : null;
    }

    public static function saveLocation(int $userId, array $d): void
    {
        $district = DB::table('districts')->where('name', $d['district'])->first() ?? throw new OrderException('Please choose a valid district.');
        $now = date('Y-m-d H:i:s');
        DB::transaction(function () use ($userId, $d, $district, $now) {
            DB::table('farmers')->where('user_id', $userId)->update([
                'division_id' => $district->division_id, 'district_id' => $district->id, 'upazila' => $d['upazila'],
                'latitude' => $d['latitude'], 'longitude' => $d['longitude'], 'updated_at' => $now,
            ]);
            DB::table('users')->where('id', $userId)->update(['division_id' => $district->division_id, 'district_id' => $district->id, 'upazila' => $d['upazila'], 'latitude' => $d['latitude'], 'longitude' => $d['longitude'], 'updated_at' => $now]);
        });
    }

    public static function saveProfile(int $userId, array $d): void
    {
        $now = date('Y-m-d H:i:s');
        DB::transaction(function () use ($userId, $d, $now) {
            DB::table('users')->where('id', $userId)->update([
                'name' => $d['name'], 'email' => $d['email'] ?: null, 'phone' => $d['phone'], 'address' => $d['address'] ?? null,
                'upazila' => $d['upazila'], 'updated_at' => $now,
            ]);
            DB::table('farmers')->where('user_id', $userId)->update([
                'farm_name' => $d['farm_name'], 'address' => $d['address'] ?? null, 'upazila' => $d['upazila'],
                'about' => $d['about'] ?? null, 'updated_at' => $now,
            ]);
        });
    }
}
