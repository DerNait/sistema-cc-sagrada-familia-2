<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividade extends Model
{
   public function gradoCurso()
   {
       return $this->belongsTo(GradoCurso::class);
   }
}
