<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\FarmDirectNotification;

class FarmDirectNotifier
{
    public static function user(int $userId, string $title, string $message, ?string $url = null): void
    {
        $user = User::find($userId);
        $user?->notify(new FarmDirectNotification($title, $message, $url));
    }

    public static function role(string $role, string $title, string $message, ?string $url = null): void
    {
        User::where('role', $role)->where('is_active', true)->each(
            fn (User $user) => $user->notify(new FarmDirectNotification($title, $message, $url))
        );
    }
}