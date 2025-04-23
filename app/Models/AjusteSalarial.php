<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AjusteSalarial extends Model
{
    protected $table = 'ajustes_salariales';

    public function tipo() {
        return $this->belongsTo(TipoAjuste::class, 'tipo_ajuste_id');
    }    
}
