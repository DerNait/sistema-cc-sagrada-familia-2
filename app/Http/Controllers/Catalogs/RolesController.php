<?php
namespace App\Http\Controllers\Catalogs;

use Illuminate\Http\Request;
use App\Http\Controllers\Crud\CrudControllerBase;
use App\Models\Role;

class RolesController extends CrudControllerBase
{
    protected function configure(Request $request): void
    {
        // 1) Asignamos el modelo
        $this->model(Role::class);

        // 2) Columnas para LIST y FORM
        $this->column('id')
             ->label('ID')
             ->readonly();

        $this->column('nombre')
             ->label('Nombre')
             ->rules(['required', 'string', 'max:255']);

        // Campos de auditoría
        $this->column('created_at')
             ->label('Creado')
             ->type('datetime')
             ->readonly();

        $this->column('updated_at')
             ->label('Actualizado')
             ->type('datetime')
             ->readonly();

		$this->action('permissions')
             ->label('Permisos')
             ->icon('fa-lock')
             ->btn('btn-outline-warning')
             ->url('/catalogos/roles/__ID__/permisos');

        // 4) Calcular permisos CRUD (modulo "usuarios")
        $this->syncAbilities('usuarios');
    }

		public function permisos() {

			$params = [
				'modulos' => [
					[
						'id' => 1,
						'modulo' => 'Usuarios',
						'permissions' => 'read, write, delete',
					],
					[
						'id' => 2,
						'modulo' => 'Roles',
						'permissions' => 'read, write',
					],
					[
						'id' => 3,
						'modulo' => 'Permisos',
						'permissions' => 'read',
					],
					[
						'id' => 4,
						'modulo' => 'Productos',
						'permissions' => 'read, write, delete',
					],
					[
						'id' => 5,
						'modulo' => 'Pedidos',
						'permissions' => 'read, write',
					],
					[
						'id' => 6,
						'modulo' => 'Reportes',
						'permissions' => 'read',
					],
					[
						'id' => 7,
						'modulo' => 'Clientes',
						'permissions' => 'read, write, delete',
					],
					[
						'id' => 8,
						'modulo' => 'Categorías',
						'permissions' => 'read, write',
					],
				]
			];

			return view('component', [
				'component' => 'roles-permisos',
				'params'    => $params,
			]);
		}
}