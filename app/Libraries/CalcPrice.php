<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Session;

class CalcPrice
{
    public static function get($precio, $coin, $exchange)
    {
        $currency = Session::get('currentCurrency', '2');

        return self::getByCurrency($precio, $coin, $exchange, $currency);
    }

    public static function getByCurrency($precio, $coin, $exchange, $currency)
    {
        $price = (float) ($precio ?? 0);
        $exchange = (float) ($exchange ?: 1);
        $coin = (string) ($coin ?? '1');
        $currency = (string) ($currency ?? '2');

        if ($coin === '1' && $currency === '2') {
            return $exchange > 0 ? $price / $exchange : $price;
        }

        if ($coin === '2' && $currency === '1') {
            return $price * $exchange;
        }

        return $price;
    }
}
