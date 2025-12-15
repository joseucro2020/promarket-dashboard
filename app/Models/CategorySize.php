<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategorySize extends Model {
    protected $table="category_sizes";

    public function size() {
        return $this->belongsTo(Size::class,'size_id');
    }

    public function productAmount() {
        return $this->hasMany(ProductAmount::class);
    }
}
