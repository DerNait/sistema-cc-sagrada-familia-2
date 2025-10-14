<?php

namespace App\Http\Controllers\Catalogs;

use Illuminate\Http\Request;
use App\Http\Controllers\Crud\CrudControllerBase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Models\Estudiante;
use App\Models\Empleado;

class UsersController extends CrudControllerBase
{
    protected function configure(Request $request): void
    {
        $this->model(User::class);

        $isCreate = $request->isMethod('post');

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
                 'unique:users,email' . ($isCreate ? '' : ',' . $request->route('id')),
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

        $this->syncAbilities('admin.usuarios');
    }

    public function store(Request $request)
    {
        $this->configure($request);
        abort_unless($this->abilities['create'] ?? false, 403);

        $data = $this->validatedData($request);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }
        unset($data['password_confirmation']);

        $user = $this->newModelQuery()->create($data);

        $role = Role::find($data['rol_id']);
        $rolNombre = strtolower($role->nombre);

        if ($rolNombre === 'estudiante') {
            Estudiante::create([
                'usuario_id' => $user->id,
                'beca_id' => 1,  // Default beca_id para estudiantes
            ]);
        } elseif (in_array($rolNombre, ['docente', 'inventario', 'secretaria', 'administracion'])) {
            Empleado::create([
                'usuario_id' => $user->id,
                'salario_base' => 0,
            ]);
        }

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

     $oldRole = Role::find($user->rol_id);
     $newRole = Role::find($data['rol_id']);

     $oldIsEstudiante = strtolower($oldRole->nombre) === 'estudiante';
     $newIsEstudiante = strtolower($newRole->nombre) === 'estudiante';

     if ($oldIsEstudiante !== $newIsEstudiante) {
          // Cambio de Estudiante <-> Empleado
          if ($oldIsEstudiante) {
               // De estudiante a empleado
               Estudiante::where('usuario_id', $user->id)->delete();
               Empleado::create([
                    'usuario_id' => $user->id,
                    'salario_base' => 0,
               ]);
          } else {
               // De empleado a estudiante
               Empleado::where('usuario_id', $user->id)->delete();
               Estudiante::create([
                    'usuario_id' => $user->id,
                    'beca_id' => 1, // default beca_id para estudiantes
               ]);
          }
     }

     $user->update($data);

     return $user->refresh();
     }

}
