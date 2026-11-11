<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Catalog queries: products, categories, farmers, districts and settings.
 * Returns plain arrays in the same shape as the demo data, so the Blade views work unchanged.
 */
class Shop
{
    private const COLORS = ['Vegetables' => '#F4E3DF', 'Fruits' => '#FBEFD2', 'Rice & Grains' => '#EFE3C8', 'Dairy' => '#E4EEF5', 'Fish & Meat' => '#DDE7EC', 'Eggs' => '#F6E2C4', 'Organic' => '#D8EBD3'];

    public static function setting(string $key, $default = null)
    {
        $v = DB::table('settings')->where('key', $key)->value('value');
        return $v === null ? $default : $v;
    }

    /** Delivery charge for a subtotal: free from the "free delivery above" amount */
    public static function deliveryCharge(int $subtotal): int
    {
        if ($subtotal <= 0) {
            return 0;
        }
        return $subtotal >= (int) self::setting('free_delivery_above', 1000) ? 0 : (int) self::setting('delivery_charge', 40);
    }

    public static function categories(): array
    {
        return DB::table('categories')->where('is_active', true)->orderBy('id')->pluck('name')->all();
    }

    public static function categoryImage(string $name): ?string
    {
        return DB::table('categories')->where('name', $name)->value('image');
    }

    public static function districts(): array
    {
        return DB::table('districts as d')->join('divisions as v', 'v.id', '=', 'd.division_id')->orderBy('d.name')->orderBy('v.name')
            ->get(['d.name as name', 'v.name as division', 'd.latitude as lat', 'd.longitude as lng'])
            ->map(fn ($r) => ['name' => $r->name, 'division' => $r->division, 'lat' => (float) $r->lat, 'lng' => (float) $r->lng])->all();
    }

    private static function productQuery()
    {
        return DB::table('products as p')
            ->join('farmers as f', 'f.id', '=', 'p.farmer_id')
            ->join('users as u', 'u.id', '=', 'f.user_id')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('districts as d', 'd.id', '=', 'f.district_id')
            ->where('p.status', 'approved')->where('f.status', 'approved')
            ->select('p.*', 'c.name as category_name', 'u.name as farmer_name', 'u.photo as farmer_photo', 'd.name as district_name');
    }

    private static function productRow(object $r): array
    {
        $gallery = [];
        if ($r->image && function_exists('public_path')) {
            $base = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '', $r->image);
            foreach ([2, 3, 4] as $n) {
                if (is_file(public_path('images/' . $base . '-' . $n . '.jpg'))) {
                    $gallery[] = $base . '-' . $n . '.jpg';
                }
            }
            if ($gallery) {
                array_unshift($gallery, $r->image);
            }
        }

        return [
            'id' => (int) $r->id, 'name' => $r->name, 'unit' => $r->unit, 'price' => (int) $r->price, 'stock' => (int) $r->stock,
            'category' => $r->category_name, 'emoji' => $r->emoji ?: '📦', 'color' => $r->image ? '#FFFFFF' : (self::COLORS[$r->category_name] ?? '#EEF6EA'),
            'farmer_id' => (int) $r->farmer_id, 'rating' => (float) $r->rating, 'reviews' => (int) $r->review_count, 'image' => $r->image,
            'farmer' => $r->farmer_name, 'district' => $r->district_name ?? '', 'farmer_photo' => $r->farmer_photo, 'gallery' => $gallery,
            'description' => $r->description,
        ];
    }

    /**
     * @param array $f category, district, q, max, sort (low|high), my_district, farmer_id, limit
     */
    public static function products(array $f = []): array
    {
        $q = self::productQuery();
        if (! empty($f['category'])) {
            $q->where('c.name', $f['category']);
        }
        if (! empty($f['district'])) {
            $q->where('d.name', $f['district']);
        }
        if (! empty($f['farmer_id'])) {
            $q->where('p.farmer_id', $f['farmer_id']);
        }
        if (! empty($f['q'])) {
            $q->where('p.name', 'like', '%' . $f['q'] . '%');
        }
        if (! empty($f['max'])) {
            $q->where('p.price', '<=', (int) $f['max']);
        }

        $sort = $f['sort'] ?? null;
        if ($sort === 'low') {
            $q->orderBy('p.price');
        } elseif ($sort === 'high') {
            $q->orderByDesc('p.price');
        } elseif (! empty($f['my_district'])) {
            // default: products from the client's own district first
            $q->orderByRaw('CASE WHEN d.name = ? THEN 0 ELSE 1 END', [$f['my_district']])->orderBy('p.id');
        } else {
            $q->orderBy('p.id');
        }
        if (! empty($f['limit'])) {
            $q->limit((int) $f['limit']);
        }

        return $q->get()->map(fn ($r) => self::productRow($r))->all();
    }

    public static function product(int $id): ?array
    {
        $r = self::productQuery()->where('p.id', $id)->first();
        return $r ? self::productRow($r) : null;
    }

    public static function reviews(int $productId, int $limit = 5): array
    {
        return DB::table('reviews as r')->join('users as u', 'u.id', '=', 'r.user_id')->where('r.product_id', $productId)
            ->orderByDesc('r.id')->limit($limit)->get(['u.name as name', 'r.rating as rating', 'r.title as title', 'r.body as body', 'r.created_at as created_at'])
            ->map(fn ($x) => ['name' => $x->name, 'rating' => (int) $x->rating, 'title' => $x->title, 'body' => $x->body, 'date' => date('d M Y', strtotime($x->created_at))])->all();
    }

    /** [stars => count] for 5..1 */
    public static function ratingBreakdown(int $productId): array
    {
        $rows = DB::table('reviews')->where('product_id', $productId)->selectRaw('rating, COUNT(*) as n')->groupBy('rating')->pluck('n', 'rating')->all();
        $out = [];
        foreach ([5, 4, 3, 2, 1] as $s) {
            $out[$s] = (int) ($rows[$s] ?? 0);
        }
        return $out;
    }

    private static function farmerRow(object $r): array
    {
        return ['id' => (int) $r->id, 'name' => $r->name, 'district' => $r->district_name ?? '', 'rating' => (float) $r->rating, 'reviews' => (int) $r->review_count,
            'photo' => $r->photo, 'about' => $r->about ?? '', 'farm_name' => $r->farm_name];
    }

    private static function farmerQuery()
    {
        return DB::table('farmers as f')->join('users as u', 'u.id', '=', 'f.user_id')->leftJoin('districts as d', 'd.id', '=', 'f.district_id')
            ->where('f.status', 'approved')->where('u.is_active', true)
            ->select('f.id', 'f.farm_name', 'f.about', 'f.rating', 'f.review_count', 'u.name', 'u.photo', 'd.name as district_name');
    }

    public static function farmers(): array
    {
        return self::farmerQuery()->orderBy('f.id')->get()->map(fn ($r) => self::farmerRow($r))->all();
    }

    public static function farmer(int $id): ?array
    {
        $r = self::farmerQuery()->where('f.id', $id)->first();
        return $r ? self::farmerRow($r) : null;
    }
}
