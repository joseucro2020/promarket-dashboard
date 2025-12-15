<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $table = "product_images";

    protected $fillable = [
        'file', 'product_id', 'main'
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute()
    {
        $file = $this->file;

        if (substr($file, 0, 4) === "http") {
            return $file;
        }

        return asset('img/products/' . $file);
    }
}
