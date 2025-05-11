<?php
namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            'Administracion',
            'Secretaria',
            'Inventario',
            'Docente',
            'Estudiante',
        ];

        foreach ($roles as $rolNombre) {
            Role::firstOrCreate(
                ['nombre' => $rolNombre],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $adminRoleId = Role::where('nombre', 'Administracion')->value('id');

        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('secret'),
                'rol_id'   => $adminRoleId,
            ]
        );
    }
}