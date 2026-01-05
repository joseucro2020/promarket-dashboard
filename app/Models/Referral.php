<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Referral extends Model
{
    protected $table = "referrals";

    protected $appends = [
        'type_name'
    ];

    use SoftDeletes;

    const FIRST_MONTH = 1;
    const SECOND_MONTH = 2;
    const THIRD_MONTH = 3;

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function getTypeNameAttribute()
    {
        $response = "";
        switch ($this->month_time) {
            case self::FIRST_MONTH:
                $response = "Primera Compra";
                break;

            case self::SECOND_MONTH:
                $response = "Cliente Habitual";
                break;

            case self::THIRD_MONTH:
                $response = "Cliente Recurrente";
                break;
        }
        return $response;
    }
}
