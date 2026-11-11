<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReviewService
{
    /** Has this user received (status = delivered) an order that contains this product? */
    public static function canReview(int $userId, int $productId): bool
    {
        return DB::table('order_items as i')->join('orders as o', 'o.id', '=', 'i.order_id')
            ->where('o.user_id', $userId)->where('o.status', 'delivered')->where('i.product_id', $productId)->exists();
    }

    public static function add(int $userId, int $productId, int $rating, ?string $title, string $body): void
    {
        if (! self::canReview($userId, $productId)) {
            throw new OrderException('You can review a product after it has been delivered to you.');
        }
        if (DB::table('reviews')->where('user_id', $userId)->where('product_id', $productId)->exists()) {
            throw new OrderException('You have already reviewed this product.');
        }

        DB::transaction(function () use ($userId, $productId, $rating, $title, $body) {
            $now = date('Y-m-d H:i:s');
            DB::table('reviews')->insert(['user_id' => $userId, 'product_id' => $productId, 'rating' => $rating, 'title' => $title, 'body' => $body, 'created_at' => $now, 'updated_at' => $now]);
            $agg = DB::table('reviews')->where('product_id', $productId)->selectRaw('AVG(rating) as avg, COUNT(*) as n')->first();
            DB::table('products')->where('id', $productId)->update(['rating' => round((float) $agg->avg, 1), 'review_count' => (int) $agg->n, 'updated_at' => $now]);
        });
    }
}
