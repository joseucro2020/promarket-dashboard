<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpecialCategory extends Model
{
    use SoftDeletes;

    protected $table = 'special_categories';

    protected $fillable = [
        'name',
        'order',
        'status',
        'slider_quantity',
        'tipo_order',
        'tipo_special',
        'slug',
    ];

    public function details()
    {
        return $this->hasMany(SpecialCategoryDetail::class, 'special_category_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'special_category_details', 'special_category_id', 'product_id');
    }
}
