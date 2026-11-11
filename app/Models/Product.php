<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'farmer_id', 'category_id', 'name', 'unit', 'price', 'stock', 'description', 'image', 'emoji',
        'harvest_date', 'status', 'stage', 'rejection_reason', 'rating', 'review_count',
    ];

    protected function casts(): array
    {
        return ['harvest_date' => 'date', 'rating' => 'float'];
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Products clients are allowed to see and buy */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'approved')->where('stock', '>', 0);
    }
}
