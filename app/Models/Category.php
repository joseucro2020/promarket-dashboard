<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name', 'name_english', 'paypal', 'stripe', 'order', 'slug', 'icon', 'icon2'
    ];

    public function subcategories()
    {
        return $this->hasMany(Subcategory::class, 'category_id');
    }

    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'category_sizes', 'category_id', 'size_id');
    }

    public function filters()
    {
        return $this->belongsToMany(Filter::class, 'category_filters', 'category_id', 'filter_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
