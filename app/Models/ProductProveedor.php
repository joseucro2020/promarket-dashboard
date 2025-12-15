<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ProductProveedor extends Model
{
    protected $table = "product_proveedor";

    use SoftDeletes;
}
