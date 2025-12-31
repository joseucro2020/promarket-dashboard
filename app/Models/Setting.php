<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    const MINIMUN_PURCHASE = 'MINIMUN_PURCHASE';

    protected $fillable = ['name', 'value'];

    public static function getMinimunPurchase()
    {
        return Setting::where('name', Setting::MINIMUN_PURCHASE)->first();
    }
}
