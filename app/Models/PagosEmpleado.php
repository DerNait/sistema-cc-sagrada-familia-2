<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagosEmpleado extends Model
{
    public function ajustes() {
        return $this->hasMany(AjusteSalarial::class, 'pago_empleado_id');
    }    
}
