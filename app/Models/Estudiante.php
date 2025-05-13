<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function beca()
    {
        return $this->belongsTo(Beca::class, 'beca_id');
    }
}
