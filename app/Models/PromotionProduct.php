<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionProduct extends Model
{
    // El nombre de la tabla en la migración es 'promotion_products'
    protected $table = 'promotion_product';

    protected $fillable = [
        'promotion_id',
        'product_id',
        'amount',
    ];

    public function product_amount()
    {
        return $this->belongsTo(ProductAmount::class, 'product_id');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }
}
