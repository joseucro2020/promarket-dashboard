<?php

namespace App\Libraries;

use Illuminate\Support\Str;

class SetNameImage
{
    public static function set(string $originalName, string $extension): string
    {
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $slug = Str::slug($name);

        return sprintf('%s-%s.%s', $slug, time(), $extension);
    }
}
