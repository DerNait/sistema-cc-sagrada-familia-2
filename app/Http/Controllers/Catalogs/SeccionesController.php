<?php

namespace App\Http\Controllers\Catalogs;

use App\Http\Controllers\Crud\CrudControllerBase;
use App\Models\Seccion;
use Illuminate\Http\Request;

class SeccionesController extends CrudControllerBase
{
    protected function configure(Request $request): void
    {
        $this->model(Seccion::class);

        $this->column('id')
            ->label('ID')
            ->readonly();

        $this->column('seccion')
            ->label('Sección');


        $this->syncAbilities('secciones');
    }
}
