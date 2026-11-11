<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLES = ['admin', 'manager', 'farmer', 'client'];

    protected $fillable = [
        'name', 'email', 'nid_hash', 'password', 'phone', 'address', 'role', 'division_id', 'district_id',
        'upazila', 'latitude', 'longitude', 'photo', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function farmer(): HasOne
    {
        return $this->hasOne(Farmer::class);
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public static function nidHashFor(string $nid): string
    {
        $normalized = self::normalizeNid($nid);

        return hash_hmac('sha256', $normalized, (string) config('app.key'));
    }

    public static function normalizeNid(string $nid): string
    {
        // Accept Bengali and Arabic-Indic keypad digits, then hash one canonical form.
        $nid = strtr($nid, [
            '০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4',
            '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        return preg_replace('/[\s-]+/', '', $nid) ?? '';
    }

    /** Route name of this user's dashboard */
    public function homeRoute(): string
    {
        return match ($this->role) {
            'admin' => 'admin.dashboard',
            'manager' => 'manager.dashboard',
            'farmer' => 'farmer.dashboard',
            default => 'client.dashboard',
        };
    }
}
