<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $fillable = [
        'name', 'name_english'
    ];

    public function designs()
    {
        return $this->hasMany(Design::class, 'collection_id');
    }
}
