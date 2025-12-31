<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BuyOrder extends Model
{
    use SoftDeletes;

    protected $table = "buyorder";

    protected $fillable = ['cond_pago', 'fecha', 'fecha_vto', 'nro_doc', 'moneda', 'proveedor_id', 'almacen_id', 'status','reason'];

    public function detalles() {
        return $this->hasMany(BuyOrderDetail::class, 'order_id');
    }    

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'proveedor_id','id');
    }
}
