<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoPago extends Model
{
    protected $table = 'tipo_pagos';

    protected $fillable = [
        'nombre'
    ];

    public function pagos()
    {
        return $this->hasMany(EstudiantePago::class, 'tipo_pago_id');
    }
}
