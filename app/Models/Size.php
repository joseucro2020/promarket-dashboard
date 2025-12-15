<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    protected $table = "sizes";

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_sizes', 'size_id', 'category_id');
    }
}
