<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProfilePhoto
{
    public static function save(?UploadedFile $photo): ?string
    {
        if (! $photo) {
            return null;
        }

        $directory = public_path('images/profiles');
        File::ensureDirectoryExists($directory);
        $filename = Str::uuid() . '.' . $photo->extension();
        $photo->move($directory, $filename);

        return 'profiles/' . $filename;
    }
}
