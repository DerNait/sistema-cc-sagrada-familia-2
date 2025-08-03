<?php

namespace App\Http\Controllers\Catalogs;

use Illuminate\Http\Request;
use App\Http\Controllers\Crud\CrudControllerBase;
use App\Models\User;
use App\Models\Role;

class UsersController extends CrudControllerBase
{
    protected function configure(Request $request): void
    {
        // 1) Asignamos el modelo
        $this->model(User::class);

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
               $request->route('id') ? 'nullable' : 'required',
               'string',
               'min:8',
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
}
