<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoMovimiento extends Model
{
    protected $table = 'tipo_movimientos';

    protected $fillable = [
        'tipo', // ejemplo: Entrada, Salida, Ajuste
    ];

    public $timestamps = true;

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'tipo_movimiento_id');
    }
}
