<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModulePermission extends Model
{
    protected $table = 'modulos_permisos';

    public function module()
    {
        return $this->belongsTo(Module::class, 'modulo_id'); // <-- clave correcta
    }

    public function menus()
    {
        return $this->hasMany(Menu::class, 'module_permission_id');
    }

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'rol_modulos_permisos',
            'modulo_permiso_id', // FK a modulos_permisos
            'rol_id'             // FK a roles
        );
    }

    /** menú principal para la tarjeta (top-level y por orden) */
    public function primaryMenu()
    {
        return $this->hasOne(Menu::class, 'module_permission_id')
            ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END') // top-level primero
            ->orderBy('order');                                           // luego por orden
    }
}