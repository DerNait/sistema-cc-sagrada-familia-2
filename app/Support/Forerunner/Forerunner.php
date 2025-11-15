<?php
namespace App\Support\Forerunner;

use Illuminate\Support\Facades\Auth;
use App\Models\{Module, ModulePermission, RoleModulePermission};

class Forerunner
{
    public static function allows(string $ability): bool
    {
        if (!\Auth::check()) return false;
        if (self::isRoot()) return true;

        // cortar por el ÚLTIMO punto: modulo puede llevar puntos
        $pos = strrpos($ability, '.');
        if ($pos === false) return false;

        $moduleKey = substr($ability, 0, $pos);        // p.ej. 'admin.secciones'
        $permKey   = substr($ability, $pos + 1);       // p.ej. 'read'

        $moduleId = \App\Models\Module::where('modulo', $moduleKey)->value('id');
        if (!$moduleId) return false;

        $permissionId = \App\Models\ModulePermission::where('modulo_id', $moduleId)
                        ->where('permiso', $permKey)
                        ->value('id');
        if (!$permissionId) return false;

        $rolId = \Auth::user()->rol_id;
        if (!$rolId) return false;

        return \App\Models\RoleModulePermission::where('rol_id', $rolId)
                ->where('modulo_permiso_id', $permissionId)
                ->exists();
    }
    public static function crudMatrix(string $module): array
    {
        return collect(['create','read','update','delete','export', 'randomize'])
            ->mapWithKeys(fn($p)=>[$p=>self::allows("$module.$p")])
            ->toArray();
    }

    public static function rootRoleId(): int
    {
        return (int) config('forerunner.root_role_id', 1);
    }

    public static function isRoot(): bool
    {
        return Auth::check() && (int) Auth::user()->rol_id === self::rootRoleId();
    }
}