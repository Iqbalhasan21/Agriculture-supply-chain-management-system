<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farmer extends Model
{
    protected $fillable = [
        'user_id', 'farm_name', 'division_id', 'district_id', 'upazila', 'address', 'latitude', 'longitude',
        'about', 'rating', 'review_count', 'is_verified', 'status', 'review_note',
    ];

    protected function casts(): array
    {
        return ['is_verified' => 'boolean', 'latitude' => 'float', 'longitude' => 'float', 'rating' => 'float'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
