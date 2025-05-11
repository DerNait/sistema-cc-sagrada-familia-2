<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{RoleModulePermission, Role, ModulePermission};

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $admin = Role::find(1);
        $allPerms = ModulePermission::pluck('id');
        $admin->permissions()->sync($allPerms);      // necesita relación belongsToMany en Role
    }
}