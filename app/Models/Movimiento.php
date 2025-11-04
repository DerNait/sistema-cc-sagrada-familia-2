<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    protected $table = 'movimientos';

    protected $fillable = [
        'producto_id',
        'tipo_movimiento_id',  // Cambiado de 'tipo' a 'tipo_movimiento_id'
        'cantidad',
        'descripcion',
        'fecha',
        'usuario_id',
    ];

    public $timestamps = false;

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function tipoMovimiento()
    {
        return $this->belongsTo(TipoMovimiento::class, 'tipo_movimiento_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}