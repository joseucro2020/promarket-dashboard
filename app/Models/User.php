<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Pais;
use App\Models\Estado;
use App\Models\Municipality;
use App\Models\Coupon;
use App\Models\Referral;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    // Feature flags / enums
    public const IS_PRO = 1;
    public const IS_NOT_PRO = 0;

    public const NATURAL = 1;
    public const JURIDICO = 2;

    // Relations
    public function pais()
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class, 'municipality_id');
    }

    public function parish()
    {
        return $this->belongsTo(\App\Models\Parish::class, 'parish_id');
    }

    public function pedidos_lastest()
    {
        return $this->hasMany(\App\Models\Purchase::class, 'user_id')->orderBy('created_at', 'desc');
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class, 'user_id');
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
