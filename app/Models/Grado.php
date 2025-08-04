<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grado extends Model
{
    protected $table = 'grado';

    protected $fillable = ['nombre'];

    public function cursos()
    {
        return $this->hasMany(Curso::class);
    }

    public function secciones()
    { 
        return $this->hasMany(Seccion::class);
    }
}
