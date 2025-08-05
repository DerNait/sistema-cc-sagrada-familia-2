<?php
namespace App\Support\Forerunner;

use Illuminate\Support\Facades\Auth;
use App\Models\{Module, ModulePermission, RoleModulePermission};

class Forerunner
{
    public static function allows(string $ability): bool
    {
        if (!Auth::check()) return false;
        if (self::isRoot()) return true;

        [$moduleKey, $permKey] = explode('.', $ability);

        $moduleId = Module::where('modulo', $moduleKey)->value('id');
        if (!$moduleId) return false;

        $permissionId = ModulePermission::where('modulo_id', $moduleId)
                                        ->where('permiso', $permKey)
                                        ->value('id');
        if (!$permissionId) return false;

        $rolId = Auth::user()->rol_id;        // ← aquí
        if (!$rolId) return false;

        return RoleModulePermission::where('rol_id', $rolId)
                                   ->where('modulo_permiso_id', $permissionId)
                                   ->exists();
    }

    public static function crudMatrix(string $module): array
    {
        return collect(['create','read','update','delete','export'])
            ->mapWithKeys(fn($p)=>[$p=>self::allows("$module.$p")])
            ->toArray();
    }

    public static function rootRoleId(): int
    {
        return (int) config('forerunner.root_role_id', 1);
    }

    public static function isRoot(): bool
    {
        return Auth::check() && Auth::user()->rol_id == self::rootRoleId();
    }
}