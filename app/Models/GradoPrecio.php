<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradoPrecio extends Model
{
    protected $table = 'grado_precio'; 

    protected $fillable = [
        'grado_id',
        'mensualidad',
        'inscripcion',
    ];

    public function pagos()
    {
        return $this->hasMany(EstudiantePago::class, 'grado_precio_id');
    }
}
