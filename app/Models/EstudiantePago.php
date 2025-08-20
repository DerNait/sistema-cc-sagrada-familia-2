<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstudiantePago extends Model
{
    // Nombre de la tabla
    protected $table = 'estudiante_pagos';

    // Campos asignables en masa
    protected $fillable = [
        'grado_precio_id',
        'estudiante_id',
        'tipo_pago_id',
        'monto_pagado',
        'meses_pagados',
        'periodo_inicio',
        'periodo_fin',
        'tipo_estado_id'
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function gradoPrecio()
    {
        return $this->belongsTo(GradoPrecio::class);
    }

    public function tipoPago()
    {
        return $this->belongsTo(TipoPago::class);
    }

    public function tipoEstado()
    {
        return $this->belongsTo(TipoEstado::class);
    }
}
