<?php

namespace App\Http\Controllers\Catalogs;

use App\Http\Controllers\Crud\CrudControllerBase;
use App\Models\Curso;
use App\Models\Grado;
use Illuminate\Http\Request;

class CursosController extends CrudControllerBase
{
    protected function configure(Request $request): void
    {
        $this->model(Curso::class);

        $this->column('id')
            ->label('ID')
            ->readonly();

        $this->column('nombre')
            ->label('Nombre')
            ->rules(['required','string','max:255']);

        /* Lista visible (concatenará nombres de los grados) */
        $this->column('grados_lista')       // atributo virtual
            ->label('Grados')
            ->readonly();                  // solo para mostrar

        /* Campo de formulario multiselección */
        $this->column('grados_id')
            ->label('Grados')
            ->type('relation')
            ->filterable('select')
            ->filterOptions(
                Grado::orderBy('nombre')->pluck('nombre', 'id')->toArray()
            )
            ->options(
                Grado::orderBy('nombre')->pluck('nombre', 'id')->toArray()
            )
            ->rules(['array', 'exists:grado,id'])
            ->pivot('grados')
            ->multiRelation()
            ->hide(); 

        $this->column('created_at')
            ->label('Creado en')
            ->type('datetime')
            ->readonly();

        $this->column('updated_at')
            ->label('Actualizado en')
            ->type('datetime')
            ->readonly();

        $this->syncAbilities('cursos');
    }
}
