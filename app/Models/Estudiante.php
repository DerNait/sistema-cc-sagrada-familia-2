<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Estudiante extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function beca()
    {
        return $this->belongsTo(Beca::class, 'beca_id');
    }

    public function seccion()
    {
        return $this->belongsTo(Seccion::class);
    }
    
    public function secciones()
    {
        return $this->belongsToMany(
            Seccion::class,
            'seccion_estudiantes',   // ← plural
            'estudiante_id',
            'seccion_id'
        );
    }
}
