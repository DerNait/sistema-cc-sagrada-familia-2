<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\{Role, ModulePermission};

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $admin = Role::find(1);
            if (!$admin) {
                $this->command->warn('Rol admin (id=1) no existe. Seeder abortado.');
                return;
            }

            // 1) Admin: TODOS los permisos
            $allPermIds = ModulePermission::pluck('id')->all();
            $admin->permissions()->sync($allPermIds);

            // 2) Resto de roles: SOLO el permiso 1
            $permId = ModulePermission::where('id', 1)->value('id');
            if (!$permId) {
                $this->command->warn('ModulePermission id=1 no existe. Seeder abortado.');
                return;
            }

            Role::where('id', '!=', $admin->id)
                ->chunkById(100, function ($roles) use ($permId) {
                    foreach ($roles as $role) {
                        // deja exactamente el permiso 1 (borra los demás si existían)
                        $role->permissions()->sync([$permId]);
                    }
                });

            $this->command->info('Permisos asignados: admin = todos; demás roles = solo permiso 1.');
        });
    }
}
