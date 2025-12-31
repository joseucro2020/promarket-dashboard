<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use SoftDeletes;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_SOLD_OUT = 2;

    protected $fillable = [
        'title',
        'limit',
        'discount_percentage',
        'start_date',
        'end_date',
        'image',
        'status',
        'order'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'discount_percentage' => 'decimal:2'
    ];

    protected $attributes = [
        'status' => self::STATUS_INACTIVE,
        'order' => 0
    ];

    protected $appends = [
        'status_name'
    ];

    public function getStatusNameAttribute()
    {
        if ($this->status == self::STATUS_ACTIVE) {
            if (($this->start_date && $this->start_date->isFuture()) || ($this->end_date && $this->end_date->isPast())) {
                return __('Promotion status inactive');
            }
        }

        switch ($this->status) {
            case self::STATUS_INACTIVE:
                return __('Promotion status inactive');
            case self::STATUS_ACTIVE:
                return __('Promotion status active');
            case self::STATUS_SOLD_OUT:
                return __('Promotion status sold_out');
            default:
                return __('Status');
        }
    }

    public function products()
    {
        return $this->hasMany(PromotionProduct::class);
    }

}
