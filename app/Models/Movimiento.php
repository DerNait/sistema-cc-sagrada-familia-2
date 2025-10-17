<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    protected $table = 'movimientos';

    protected $fillable = [
        'producto_id',
        'tipo_movimiento_id',
        'usuario_id',
        'cantidad',
        'stock_pre',
        'stock_post',
        'nombre',
        'descripcion',
        'fecha',
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

    // Retorna la diferencia entre el stock nuevo y el anterior
    public function getDiferenciaAttribute()
    {
        return $this->stock_post - $this->stock_pre;
    }

    // Formatea la fecha al estilo local
    public function getFechaFormateadaAttribute()
    {
        return date('d/m/Y H:i', strtotime($this->fecha));
    }
}
