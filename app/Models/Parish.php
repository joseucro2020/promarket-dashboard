<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parish extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = ['name', 'municipality_id'];
    
    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }
}
