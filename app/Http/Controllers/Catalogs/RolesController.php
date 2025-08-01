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

        // 4) Calcular permisos CRUD (modulo "usuarios")
        $this->syncAbilities('usuarios');
    }
}