<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Session;

class Money
{
    public static function get($cant)
    {
        $currency = Session::get('currentCurrency', '2');

        return number_format((float) ($cant ?? 0), 2, '.', ',') . ($currency == '1' ? ' Bs.' : ' USD');
    }

    public static function getByCurrency($cant, $currency)
    {
        return number_format((float) ($cant ?? 0), 2, '.', ',') . ($currency == '1' ? ' Bs.' : ' USD');
    }
}
