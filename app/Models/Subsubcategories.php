<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subsubcategories extends Model
{
    const STATUS_ACTIVE = '1';
    const STATUS_INACTIVE = '0';
    const STATUS_DELETED = '2';

    protected $fillable = ['name', 'name_english', 'subcategory_id', 'status', 'slug', 'icon'];

    protected $hidden = ['created_at', 'updated_at'];

    public function subcategories()
    {
        return $this->belongsTo(Subcategory::class, 'subcategory_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'subsubcategory_id')->where('status', '!=', '2');
    }
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
