<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    /**
     * Submenús (hijos de este menú).
     */
    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id');
    }

    /**
     * Menú padre (si aplica).
     */
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Permiso asociado al menú.
     */
    public function permission()
    {
        return $this->belongsTo(ModulePermission::class, 'module_permission_id');
    }
}
