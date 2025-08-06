<?php

namespace App\Http\Controllers\Catalogs;

use Illuminate\Http\Request;
use App\Models\Actividad;
use App\Models\GradoCurso;
use App\Http\Controllers\Crud\CrudControllerBase;

class ActividadesController extends CrudControllerBase
{
    protected function configure(Request $request): void{

        $this->model(Actividad::class);

        $gradoCursoOptions = GradoCurso::with(['grado', 'curso'])
            ->get()
            ->mapWithKeys(function ($gc) {
                return [$gc->id => $gc->grado->nombre . ' / ' . $gc->curso->nombre];
            })->toArray();

        $this->column('id')
            ->label('ID')
            ->readonly();
        
        $this->column('nombre')
            ->label('Nombre')
            ->type('text')
            ->rules(['required', 'string', 'max:255']);
        
        $this->column('grado_curso_id')
            ->label('Grado / Curso')
            ->type('select')
            ->options($gradoCursoOptions)
            ->rules(['required']);

        $this->column('created_at')
            ->label('Creado en')
            ->type('datetime')
            ->readonly();
        
        $this->column('updated_at')
            ->label('Actualizado en')
            ->type('datetime')
            ->readonly();
        
        $this->syncAbilities('actividades');
    }
}
