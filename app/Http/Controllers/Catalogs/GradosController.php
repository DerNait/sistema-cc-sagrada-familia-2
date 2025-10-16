<?php

namespace App\Http\Controllers\Catalogs;

use Illuminate\Http\Request;
use App\Http\Controllers\Crud\CrudControllerBase;
use App\Models\Grado;

class GradosController extends CrudControllerBase
{
    protected function configure(Request $request): void
    {
        $this->model(Grado::class);

        $this->column('id')
            ->label('ID')
            ->readonly();

        $this->column('nombre')
            ->label('Nombre')
            ->rules(['required', 'string', 'max:100'])
            ->filterable('text');

        $this->column('year')
            ->label('Año')
            ->type('numeric')
            ->rules(['required', 'integer', 'min:1900', 'max:2100'])
            ->filterable('numeric');

        $this->column('created_at')->label('Creado')->type('datetime')->readonly()->hide();
        $this->column('updated_at')->label('Actualizado')->type('datetime')->readonly()->hide();

        $this->syncAbilities('admin.grados');
    }
}
