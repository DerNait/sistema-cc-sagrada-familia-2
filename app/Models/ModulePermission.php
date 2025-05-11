<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModulePermission extends Model
{
    protected $table = 'modulos_permisos';

    public function module() { 
        return $this->belongsTo(Module::class, 'modulo_id'); 
    }

    public function roles()  { 
        return $this->belongsToMany(Role::class, 'rol_modulos_permisos', 'modulo_permiso_id', 'rol_id'); 
    }
}