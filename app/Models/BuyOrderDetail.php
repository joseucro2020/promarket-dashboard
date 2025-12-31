<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuyOrderDetail extends Model
{
    protected $table = 'buyorder_detail';

    protected $fillable = [
        'order_id', 'product_id', 'cantidad', 'costo', 'iva', 'total', 'precio', 'utilidad', 'existing'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function order()
    {
        return $this->belongsTo(BuyOrder::class, 'order_id');
    }

    public function product_amount()
    {
        return $this->belongsTo(ProductAmount::class, 'product_id');
    }
}
