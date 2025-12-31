<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    const STATUS_ACTIVE = 1;

    protected $fillable = ['name', 'estado_id', 'status', 'free'];

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

    public function parishes()
    {
        return $this->hasMany(Parish::class);
    }
}
