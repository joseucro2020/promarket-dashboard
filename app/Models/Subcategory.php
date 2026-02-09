<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    protected $fillable = [
        'category_id', 'name', 'name_english', 'slug', 'icon', 'status'
    ];

    public function categories()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'subcategory_id')->where('status', '!=', '2');
    }

    public function sub_subcategories()
    {
        return $this->hasMany(Subsubcategories::class, 'subcategory_id');
    }
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
