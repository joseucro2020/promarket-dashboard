<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSubcategory extends Pivot
{
    use SoftDeletes;

    protected $table = "product_subcategories";

    public $timestamps = true;
}
