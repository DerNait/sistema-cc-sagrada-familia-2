<?php
namespace App\Http\Controllers\Catalogs;

use Illuminate\Http\Request;
use App\Http\Controllers\Crud\CrudControllerBase;
use App\Models\Role;
use App\Models\Module;

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
             ->url('/admin/roles/__ID__/permisos');

        // 4) Calcular permisos CRUD (modulo "usuarios")
        $this->syncAbilities('admin.roles');
    }

	public function permisos(Request $request, int $rolId)
	{
		$rol = Role::with('permissions:id')->findOrFail($rolId);

		$rolPermisosIds = $rol->permissions->pluck('id')->all();

		$modulos = Module::with('permissions:id,modulo_id,permiso')
			->orderBy('modulo')
			->get()
			->map(function ($m) use ($rolPermisosIds) {
				return [
					'id'        => $m->id,
					'modulo'    => $m->modulo,
					'permisos'  => $m->permissions->map(function ($p) use ($rolPermisosIds) {
						return [
							'id'      => $p->id,
							'nombre'  => $p->permiso,
							'checked' => in_array($p->id, $rolPermisosIds, true),
						];
					}),
				];
			});

		return view('component', [
			'component' => 'roles-permisos',
			'params'    => [
				'rol'     => $rol,
				'modulos' => $modulos,
			],
		]);
	}
}