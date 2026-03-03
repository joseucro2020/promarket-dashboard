<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PurchaseDetails extends Model
{
    protected $table = 'purchase_details';

    protected $appends = [
        'product',
        'producto',
        'product_color',
        'product_size',
        'discounts_text',
        'unit',
        'presentation'
    ];

    public function purchase()
    {
        return $this->belongsTo('App\\Models\\Purchase', 'purchase_id');
    }

    public function product_amount()
    {
        return $this->belongsTo('App\\Models\\ProductAmount', 'product_amount_id');
    }

    public function wholesaler()
    {
        return $this->belongsTo('App\\Models\\Wholesaler', 'wholesalers_id');
    }

    public function getProductAttribute($value)
    {
        if (!Auth::check() || Auth::user()->type == 1) {
            return $this->product_amount != null && $this->product_amount->product_color != null
                ? $this->product_amount->product_color->product
                : null;
        }

        return $this->wholesaler();
    }

    public function getPresentationAttribute()
    {
        if (!is_null($this->product_amount) && $this->product_amount->presentation != 0) {
            return '- ' . $this->product_amount->presentation;
        }

        return null;
    }

    public function getUnitAttribute()
    {
        if (!is_null($this->product_amount) && $this->product_amount->unit != 0) {
            return Product::UNITS[$this->product_amount->unit] . '.';
        }

        return null;
    }

    public function getProductoAttribute($value)
    {
        if (!is_null($this->product_amount_id)) {
            return $this->product_amount != null && $this->product_amount->product_color != null
                ? $this->product_amount->product_color->product
                : null;
        }

        return $this->wholesaler;
    }

    public function getProductColorAttribute($value)
    {
        return $this->product_amount != null ? $this->product_amount->product_color : null;
    }

    public function getProductSizeAttribute($value)
    {
        return $this->product_amount != null && $this->product_amount->category_size != null
            ? $this->product_amount->category_size->size
            : null;
    }

    public function getDiscountsTextAttribute()
    {
        if ($this->offer_description != null && $this->discount_description != null) {
            return ' / ' . $this->offer_description . ' / ' . $this->discount_description;
        }

        if ($this->offer_description != null) {
            return ' / ' . $this->offer_description;
        }

        if ($this->discount_description != null) {
            return ' / ' . $this->discount_description;
        }

        return '';
    }
}
