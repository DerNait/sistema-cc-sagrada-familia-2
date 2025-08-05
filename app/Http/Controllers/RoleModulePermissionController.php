<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleModulePermissionController extends Controller
{
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'modulo_permiso_ids'   => 'array',
            'modulo_permiso_ids.*' => 'exists:modulos_permisos,id',
        ]);

        $role->permissions()
             ->sync($validated['modulo_permiso_ids'] ?? []);

        return response()->json(['ok' => true]);
    }
}
