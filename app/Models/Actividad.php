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

    public function grado()
    {
        return $this->hasOneThrough(
            Grado::class,
            GradoCurso::class,
            'id',          // local key en GradoCurso
            'id',          // local key en Grado
            'grado_curso_id',
            'grado_id'
        );
    }

    public function notas()
    {
        return $this->hasMany(EstudianteNota::class, 'actividad_id');
    }
}
