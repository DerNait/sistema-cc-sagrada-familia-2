<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradoPrecio extends Model
{
    protected $table = 'grado_precio'; 

    protected $guarded = [
        'id',
    ];

    public function pagos()
    {
        return $this->hasMany(EstudiantePago::class, 'grado_precio_id');
    }
}
