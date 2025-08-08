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
            new NavModule('Home', 'home', 100, 'fas fa-home', null, NavModule::READ),
            new NavModule('Dashboard','dashboard',200,'fas fa-chart-pie', null, NavModule::READ),
            new NavModule('Cursos','cursos',300,'fas fa-graduation-cap', null, NavModule::READ),
            new NavModule('Catálogos','catalogos',400,'fas fa-book', null, NavModule::READ),
            new NavModule('Usuarios', 'catalogos.usuarios', 100, 'fas fa-user', 'catalogos', array_merge(NavModule::CRUD, NavModule::EXPORT)),
            new NavModule('Roles', 'catalogos.roles', 200, 'fas fa-key', 'catalogos', array_merge(NavModule::CRUD, NavModule::EXPORT)),
            new NavModule('Empleados','catalogos.empleados', 300,'fas fa-user','catalogos', array_merge(NavModule::CRUD, NavModule::EXPORT)),
            new NavModule('Estudiantes','catalogos.estudiantes', 400,'fas fa-user','catalogos', array_merge(NavModule::CRUD, NavModule::EXPORT)),
            new NavModule('Productos','catalogos.productos', 600,'fas fa-user','catalogos', array_merge(NavModule::CRUD, NavModule::EXPORT)),
            new NavModule('Cursos','catalogos.cursos', 700,'fas fa-user','catalogos', array_merge(NavModule::CRUD, NavModule::EXPORT)),
            new NavModule('Secciones','catalogos.secciones', 800,'fas fa-user','catalogos', array_merge(NavModule::CRUD, NavModule::EXPORT, NavModule::RANDOMIZE)),
            new NavModule('Actividades', 'catalogos.actividades', 900, 'fas fa-user', 'catalogos', array_merge(NavModule::CRUD, NavModule::EXPORT)),
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