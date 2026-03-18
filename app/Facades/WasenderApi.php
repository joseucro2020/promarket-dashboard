<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class WasenderApi extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'wasender.client';
    }
}
