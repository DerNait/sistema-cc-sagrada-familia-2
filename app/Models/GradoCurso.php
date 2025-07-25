<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradoCurso extends Model
{
    protected $table = 'grado_cursos';

    public function grado()
    {
        return $this->belongsTo(Grado::class);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }
}
