<?php

namespace App\Models;

use ArmorPayments\Api\Orders;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    //
    protected $table = 'proveedor';

    // The table uses the default primary key `id` (int auto-increment).
    // `id_prove` is a supplier identification number (RIF/CI) and is NOT the primary key.

    protected $fillable = [
        'id_prove' ,
        'tipo_prove',
        'nombre_prove',
        'proced_prove' ,
        'direcc_prove' ,
        'pais_prove' ,
        'estado_prove' ,
        'muni_prove' ,
        'postal_prove' ,
        'tlf_prove',
        'rsp_prove',
        'email_prove',
        'status_prove',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function pais() {
        return $this->hasOne(Pais::class);
    }

    public function estado() {
        return $this->hasOne(Estado::class);
    }

    public function municipio() {
        return $this->hasOne(Municipality::class);
    }

    public function ordenesCompra() {
        return $this->hasOne(BuyOrder::class);
    }
}
