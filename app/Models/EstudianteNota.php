<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EstudianteNota extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function actividad()
    {
        return $this->belongsTo(Actividad::class);
    }
}
