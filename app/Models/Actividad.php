<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $guarded = ['id'];
    protected $table = 'actividades';

    protected $appends = [
        'grado_name',
        'curso_name',
        'grado_id',
        'curso_id',
    ];

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

    public function getGradoIdAttribute()
    {
        return optional($this->gradoCurso)->grado_id;
    }

    public function getCursoIdAttribute()
    {
        return optional($this->gradoCurso)->curso_id;
    }

    public function getGradoNameAttribute()
    {
        return $this->gradoCurso?->grado?->nombre;
    }

    public function getCursoNameAttribute()
    {
        return $this->gradoCurso?->curso?->nombre;
    }
}
