<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAmount extends Model {
    
    use \Illuminate\Database\Eloquent\SoftDeletes;

    const UNITS = ['', 'Gr', 'Kg', 'Ml', 'L', 'Cm'];

    protected $table = "product_amount";

    public function product_color() {
        return $this->belongsTo(ProductColor::class,'product_color_id');
    }
    
    public function product() {
        return $this->belongsTo(Product::class, 'product_color_id', 'id');
    }

    public function category_size() {
        return $this->belongsTo(CategorySize::class,'category_size_id');
    }
}
