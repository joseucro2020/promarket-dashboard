<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use SoftDeletes;

    /**
     * Fillable of the table
     */
    protected $fillable = [
        'status',
        'type',
        'limit',
        'name',
        'percentage',
        'start',
        'end',
        'quantity_product',
        'minimum_purchase',
        'quantity_purchase'
    ];

    protected $appends = [
        'type_text',
        'type_unit',
    ];

    /**
     * 
     * Statuses
     */
    const ACTIVE = 1;
    const INACTIVE = 0;

    /**
     * Discount types texts
     */
    const TYPES = [
        'first_buy' => 'Primera Compra',
        'quantity_product' => 'Cantidad de productos',
        'minimum_purchase' => 'Monto minimo de compra',
        'quantity_purchase' => 'Cantidad de compras'
    ];
    const TYPES_UNITIES = [
        'first_buy' => '',
        'quantity_product' => 'unid.',
        'minimum_purchase' => '$',
        'quantity_purchase' => ''
    ];

    /**
     * 
     * Relationships
     */
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    /**
     * Appends attributes
     */
    public function getTypeTextAttribute()
    {
        return $this::TYPES[$this->type];
    }

    public function getTypeUnitAttribute()
    {
        return $this::TYPES_UNITIES[$this->type];
    }

    public function detail()
    {
        return $this->hasMany(PurchaseDetails::class);
    }
}
