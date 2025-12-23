<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Coupon extends Model
{
    use SoftDeletes;

    protected $table = 'coupons';

    protected $fillable = [
        'user_id',
        'code',
        'uses',
        'discount_percentage',
        'first_purchase',
        'common_purchase',
        'recurrent_purchase',
        'status'
    ];

    protected $attributes = [
        'status' => self::STATUS_INACTIVE,
    ];

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    protected $appends = [
        'status_name'
    ];

    public function getStatusNameAttribute()
    {
        if ($this->status === self::STATUS_ACTIVE) {
            return 'Activo';
        }

        if ($this->status === self::STATUS_INACTIVE) {
            return 'Inactivo';
        }

        return 'Estado';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
