<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    protected $fillable = [
        'nombre',
        'created_at',
        'updated_at',
    ];

    protected $appends = ['grados_lista', 'grados_id']; 

    public function grados()
    {
        return $this->belongsToMany(Grado::class, 'grado_cursos');
    }

    public function getGradosListaAttribute(): string
    {
        // "Primero, Segundo, Tercero"
        return $this->grados->pluck('nombre')->implode(', ');
    }

    public function getGradosIdAttribute()
    {
        // Devuelve array de IDs de grados para marcar en el multiselect
        return $this->grados->pluck('id')->toArray();
    }
}
