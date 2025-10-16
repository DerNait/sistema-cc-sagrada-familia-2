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
            new NavModule('Pagos','pagos',400,'fas fa-credit-card', null, array_merge(NavModule::CRUD)),
            new NavModule('Admin','admin',500,'fas fa-book', null, NavModule::READ),
            new NavModule('Inventario', 'inventario',600,'fas fa-warehouse',null, NavModule::READ),

            new NavModule('Usuarios', 'admin.usuarios', 100, 'fas fa-user-shield', 'admin', array_merge(NavModule::CRUD, NavModule::EXPORT)),
            new NavModule('Roles', 'admin.roles', 200, 'fas fa-user-tag', 'admin', array_merge(NavModule::CRUD, NavModule::EXPORT)),
            new NavModule('Empleados','admin.empleados', 300,'fas fa-id-badge','admin', array_merge(NavModule::CRUD, NavModule::EXPORT)),
            new NavModule('Estudiantes','admin.estudiantes', 400,'fas fa-user-graduate','admin', array_merge(NavModule::CRUD, NavModule::EXPORT)),
            new NavModule('Productos','admin.productos', 600,'fas fa-box-open','admin', array_merge(NavModule::CRUD, NavModule::EXPORT)),
            new NavModule('Cursos','admin.cursos', 700,'fas fa-chalkboard-teacher','admin', array_merge(NavModule::CRUD, NavModule::EXPORT)),
            new NavModule('Grados', 'admin.grados', 1000, 'fas fa-school', 'admin', array_merge(NavModule::CRUD, NavModule::EXPORT)),
            new NavModule('Secciones','admin.secciones', 800,'fas fa-th-large','admin', array_merge(NavModule::CRUD, NavModule::EXPORT)),
            new NavModule('Actividades', 'admin.actividades', 900, 'fas fa-tasks', 'admin', array_merge(NavModule::CRUD, NavModule::EXPORT)),
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
