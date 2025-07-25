<?php


namespace App\Http\Controllers\Catalogs;

use App\Http\Controllers\Crud\CrudControllerBase;
use App\Models\Seccion;
use App\Models\User;
use App\Models\Grado;
use Illuminate\Http\Request;

class SeccionesController extends CrudControllerBase
{
    protected function configure(Request $request): void
    {
        $this->model(Seccion::class);

        $this->column('id')
            ->label('ID')
            ->readonly();

        $this->column('maestro_guia_id')
            ->label('Maestro guía')
            ->type('relation')
            ->rules(['required'])
            ->options(User::pluck('name', 'id')->toArray());

        $this->column('grado_id')
            ->label('Grado')
            ->type('relation')
            ->rules(['required'])
            ->options(Grado::pluck('nombre', 'id')->toArray());

        $this->column('seccion')
            ->label('Sección')
            ->rules(['required', 'string', 'max:255']);


        $this->syncAbilities('secciones');
    }
}
