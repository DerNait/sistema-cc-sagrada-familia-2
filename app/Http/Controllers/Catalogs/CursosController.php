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
            
        // Imagen (URL devuelta por el endpoint de subida)
        $this->column('imagen')
            ->label('Imagen')
            ->type('file')
            ->options([
                'accept'     => 'image/*',
                'uploadUrl'  => '/upload', // opcional; usa el props.uploadUrl si no lo pones
                'path'       => 'images/cursos',  // opcional; el backend puede guardarlo allí
                'buttonText' => 'Subir imagen',
                'placeholder'=> 'URL del archivo (se llena al subir)',
            ])
            ->rules(['nullable','string','max:255'])
            ->hide();

        // Color (hex)
        $this->column('color')
            ->label('Color')
            ->type('color')
            ->options([
                'placeholder' => '#RRGGBB',
            ])
            ->rules([
                'nullable',
                'regex:/^#([0-9A-Fa-f]{6})$/',
            ])
            ->hide();

        $this->column('icono')
            ->label('Icono')
            ->type('relation')
            ->filterable('select')
            ->filterOptions([
                'fas fa-book' => 'Libro',
                'fas fa-chalkboard-teacher' => 'Profesor',
                'fas fa-graduation-cap' => 'Graduación',
                'fas fa-users' => 'Usuarios',
                // Agregar más íconos 
            ])
            ->options([
                'fas fa-book' => 'Libro',
                'fas fa-chalkboard-teacher' => 'Profesor',
                'fas fa-graduation-cap' => 'Graduación',
                'fas fa-users' => 'Usuarios',
                // Agregar más íconos
            ])
            ->rules(['required', 'string'])
            ->hide(false);

        $this->column('created_at')
            ->label('Creado en')
            ->type('datetime')
            ->readonly();

        $this->column('updated_at')
            ->label('Actualizado en')
            ->type('datetime')
            ->readonly();

        $this->syncAbilities('catalogos.cursos');
    }
}
