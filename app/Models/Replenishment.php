<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Replenishment extends Model
{
    protected $table = 'replenishments';

    public function presentation()
    {
        return $this->hasOne('App\\Models\\ProductAmount', 'id', 'product_presentation');
    }

    public function user()
    {
        return $this->hasOne('App\\User', 'id', 'user_id');
    }

    public function purchase()
    {
        return $this->hasOne('App\\Models\\Purchase', 'id', 'purchase_id');
    }
}
