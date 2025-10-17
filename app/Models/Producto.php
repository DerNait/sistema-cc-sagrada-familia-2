<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = [
        'tipo_producto_id',
        'nombre',
        'fecha_ingreso',
        'precio_unitario',
        'cantidad',
        'created_at',
        'updated_at',
    ];
    public function tipo_producto() {
        return $this->belongsTo(TipoProducto::class, 'tipo_producto_id');
    }

    public function movimientos(){
        return $this->hasMany(Movimiento::class);
    }

    public function bolsas()
    {
        return $this->belongsToMany(
            Bolsa::class,
            'bolsas_detalles',
            'producto_id',
            'bolsa_id'
        );
    }
}
