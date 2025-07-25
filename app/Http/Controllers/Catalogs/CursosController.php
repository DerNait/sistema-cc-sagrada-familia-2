<?php

namespace App\Http\Controllers\Catalogs;

use App\Http\Controllers\Crud\CrudControllerBase;
use App\Models\Curso;
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
