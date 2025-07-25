<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $table = 'actividades';

    public function gradoCurso()
    {
        return $this->belongsTo(GradoCurso::class);
    }

    // Actividad.php
    public function notas()
    {
        return $this->hasMany(EstudianteNota::class, 'actividad_id');
    }
}
