<?php

namespace App\Http\Controllers\Catalogs;

use Illuminate\Http\Request;
use App\Http\Controllers\Crud\CrudControllerBase;
use App\Models\Estudiante;
use App\Models\Beca;
use App\Models\User;


class EstudiantesController extends CrudControllerBase
{
    protected function configure(Request $request): void
    {

        $this->model(Estudiante::class);

        $this->column('id')
            ->label('ID')
            ->readonly();

        $this->column('usuario_id')
            ->label('Estudiante')
            ->type('relation')
            ->rules(['required'])
            ->options(
                User::orderBy('name')
                    ->get()
                    ->mapWithKeys(fn($n)=>[$n->id => "{$n->name} {$n->apellido}"])
                    ->toArray()
            );

        $this->column('beca_id')
            ->label('Descuento')
            ->type('relation')
            ->rules(['required'])
            ->options(
                Beca::orderBy('descuento')
                    ->get()
                    ->mapWithKeys(fn($d)=>[$d->id => "{$d->descuento}"])
                    ->toArray()
            );

        $this->column('created_at')
            ->label('Creado en')
            ->type('datetime')
            ->readonly();
        
        $this->column('updated_at')
            ->label('Actualizado en')
            ->type('datetime')
            ->readonly();

        $this->syncAbilities('admin.estudiantes');
    }
}
