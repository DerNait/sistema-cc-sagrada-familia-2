<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Module, ModulePermission, Menu};
use App\Navigation\NavModule;

class NavigationSeeder extends Seeder
{
    public function run()
    {
        // 1) Borra todo para no acumular duplicados:
        ModulePermission::truncate();
        Module::truncate();
        Menu::truncate();

        // 2) Definir el nav
        $modules = collect([
            new NavModule('Dashboard','dashboard',100,'fas fa-home',   null, NavModule::READ),
            new NavModule('Cursos','cursos',200,'fas fa-courses',null, NavModule::READ),
            new NavModule('Catálogos','catalogos',300,'fas fa-book',    null, NavModule::READ),
            new NavModule('Usuarios', 'usuarios', 100, 'fas fa-user', 'catalogos', NavModule::READ),
            new NavModule('Empleados','empleados',200,'fas fa-user','catalogos', NavModule::READ),
            new NavModule('Estudiantes','estudiantes',300,'fas fa-user','catalogos', NavModule::READ),
            new NavModule('Productos','productos',500,'fas fa-user','catalogos', NavModule::READ),
            new NavModule('Cursos','cursos',600,'fas fa-user','catalogos', NavModule::READ),
            new NavModule('Actividades', 'actividades', 700, 'fas fa-user', 'catalogos', NavModule::READ),
        ]);

        // 3) Inserta módulos y permisos
        $saved = [];
        foreach ($modules as $m) {
            $mod = Module::firstOrCreate(['modulo' => $m->key]);
            foreach ($m->actions as $act) {
                $perm = ModulePermission::firstOrCreate([
                    'modulo_id' => $mod->id,
                    'permiso'   => $act,
                ]);
                if ($act === $m->menuAction) {
                    $saved[$m->key]['menuPerm'] = $perm->id;
                }
            }
            $saved[$m->key]['id'] = $mod->id;
        }

        // 4) Armar menu
        foreach ($modules as $m) {
            $fullRoute = $m->routeName;

            Menu::firstOrCreate(
                ['route' => $fullRoute],
                [
                    'parent_id'            => $m->parentKey
                        ? Menu::where('route', $m->parentKey . '.index')->value('id')
                        : null,
                    'module_permission_id' => $saved[$m->key]['menuPerm'] ?? null,
                    'name'                 => $m->label,
                    'order'                => $m->order,
                    'icon'                 => $m->icon,
                ]
            );
        }
    }
}