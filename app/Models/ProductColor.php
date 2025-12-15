<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductColor extends Model {
    protected $table = "product_colors";
    
    protected $fillable = [
        'name', 'name_english'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }
    
    public function amounts()
    {
        return $this->hasMany(ProductAmount::class, 'product_color_id');
    }
}
