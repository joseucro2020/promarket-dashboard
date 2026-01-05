<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialCategoryDetail extends Model
{
    protected $table = 'special_category_details';

    protected $fillable = [
        'product_id',
        'special_category_id',
    ];
}
