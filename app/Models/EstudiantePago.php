<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstudiantePago extends Model
{
    protected $table = 'estudiante_pagos';

    protected $guarded = ['id'];

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
