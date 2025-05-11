<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    public function permissions()
    {
        return $this->belongsToMany(
            ModulePermission::class,
            'rol_modulos_permisos',
            'rol_id',
            'modulo_permiso_id'
        );
    }

    public function users()
    {
        return $this->hasMany(User::class, 'rol_id');
    }
}