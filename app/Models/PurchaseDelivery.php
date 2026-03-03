<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PurchaseDelivery extends Model
{
    protected $table = 'purchase_deliveries';

    const TURNS = ['', 'Mañana', 'Tarde', 'Noche'];

    protected $fillable = [
        'purchase_id',
        'state_id',
        'municipality_id',
        'parish_id',
        'address',
        'type',
        'date',
        'turn',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function state()
    {
        return $this->belongsTo(Estado::class, 'state_id');
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class, 'municipality_id');
    }

    public function parish()
    {
        return $this->belongsTo(Parish::class, 'parish_id');
    }

    public function getTurnFormatedAttribute()
    {
        $turnKey = $this->turn;

        if ($turnKey === null || $turnKey === '') {
            return '';
        }

        if (!is_int($turnKey) && is_numeric($turnKey)) {
            $turnKey = (int) $turnKey;
        }

        return isset(self::TURNS[$turnKey]) ? self::TURNS[$turnKey] : '';
    }

    public function getDateFormatedAttribute()
    {
        if (!$this->date) {
            return '';
        }

        try {
            return Carbon::parse($this->date)->format('m/d/Y');
        } catch (\Exception $exception) {
            return '';
        }
    }
}
