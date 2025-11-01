<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SeccionEstudiante extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function seccion()
    {
        return $this->belongsTo(Seccion::class);
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }
}
