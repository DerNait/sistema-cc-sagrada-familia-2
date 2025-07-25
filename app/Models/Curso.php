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

    public function grado()
    {
        return $this->belongsTo(Grado::class);
    }
}
