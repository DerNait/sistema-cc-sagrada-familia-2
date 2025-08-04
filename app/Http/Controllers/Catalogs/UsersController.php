<?php

namespace App\Http\Controllers\Catalogs;

use Illuminate\Http\Request;
use App\Http\Controllers\Crud\CrudControllerBase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UsersController extends CrudControllerBase
{
    protected function configure(Request $request): void
    {
          // 1) Asignamos el modelo
          $this->model(User::class);

		  $isCreate = $request->isMethod('post'); 

          // 2) Columnas para LIST y FORM
          $this->column('id')
               ->label('ID')
               ->readonly();

          $this->column('name')
               ->label('Nombre')
               ->rules(['required', 'string', 'max:255']);

          $this->column('apellido')
               ->label('Apellido')
               ->rules(['nullable', 'string', 'max:255']);

          $this->column('email')
               ->label('Correo')
               ->rules([
                    'required',
                    'email',
                    'max:255',
                    'unique:users,email,' . ($request->route('id') ?? 'NULL'),
               ]);

          $this->column('password')
				->label('Contraseña')
				->type('password')
				->rules([
					$isCreate ? 'required' : 'nullable',
					'string','min:8','confirmed',
					'regex:/[a-z]/','regex:/[A-Z]/',
					'regex:/[0-9]/','regex:/[@$!%*#?&]/'
				])
				->hide();

          $this->column('rol_id')
               ->label('Rol')
               ->type('relation')
               ->filterable('select')
               ->filterOptions(
                    Role::orderBy('nombre')->pluck('nombre', 'id')->toArray()
               )
               ->options(
                    Role::orderBy('nombre')->pluck('nombre', 'id')->toArray()
               )
               ->rules(['required', 'exists:roles,id']);

          $this->column('fecha_registro')
               ->label('Fecha registro')
               ->type('date')
               ->rules(['nullable', 'date']);

          $this->column('fecha_nacimiento')
               ->label('Fecha nacimiento')
               ->type('date')
               ->rules(['nullable', 'date']);

          
          $this->column('created_at')
               ->label('Creado')
               ->type('datetime')
               ->readonly()
               ->hide();

          $this->column('updated_at')
               ->label('Actualizado')
               ->type('datetime')
               ->readonly()
               ->hide();

          // 4) Calcular permisos CRUD (modulo "usuarios")
          $this->syncAbilities('usuarios');
    }

	public function store(Request $request)
	{
		/* inicializa columnas y permisos */
		$this->configure($request);
		abort_unless($this->abilities['create'] ?? false, 403);

		$data = $this->validatedData($request);

		if ($request->filled('password')) {
			$data['password'] = Hash::make($request->input('password'));
		}
		unset($data['password_confirmation']);

		$user = $this->newModelQuery()->create($data);
		return $user->refresh();
	}

	public function update(Request $request, $id)
	{
		$this->configure($request);
		abort_unless($this->abilities['update'] ?? false, 403);

		$user = $this->newModelQuery()->findOrFail($id);
		$data = $this->validatedData($request);

		if ($request->filled('password')) {
			$data['password'] = Hash::make($request->input('password'));
		} else {
			unset($data['password']);
		}
		unset($data['password_confirmation']);

		$user->update($data);
		return $user->refresh();
	}
}
