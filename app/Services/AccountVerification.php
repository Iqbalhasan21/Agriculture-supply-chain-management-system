<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AccountVerification
{
    public static function isVerified(int $userId): bool
    {
        return DB::table('user_verifications')->where('user_id', $userId)->where('status', 'verified')->exists();
    }

    public static function status(int $userId): string
    {
        return DB::table('user_verifications')->where('user_id', $userId)->value('status') ?: 'not_submitted';
    }

    public static function invalidateAfterPhoneChange(int $userId, string $role): void
    {
        $now = now();
        DB::table('user_verifications')->insertOrIgnore([
            'user_id' => $userId, 'status' => 'not_submitted', 'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('user_verifications')->where('user_id', $userId)->update([
            'status' => 'not_submitted', 'id_front_path' => null, 'id_back_path' => null, 'face_path' => null,
            'nid_encrypted' => null, 'consent_at' => null, 'submitted_at' => null,
            'phone_verified_at' => null, 'nid_verified_at' => null, 'face_verified_at' => null,
            'reviewed_by' => null, 'reviewed_at' => null, 'review_note' => 'Your phone number changed. Submit identity verification again.',
            'updated_at' => $now,
        ]);
        if ($role === 'farmer') {
            DB::table('farmers')->where('user_id', $userId)->update([
                'status' => 'pending', 'is_verified' => false, 'review_note' => null, 'updated_at' => $now,
            ]);
        }
    }
}
