<?php

namespace App\Http\Controllers\Catalogs;

use App\Http\Controllers\Crud\CrudControllerBase;
use App\Models\Seccion;
use App\Models\Empleado;
use App\Models\Estudiante;
use App\Models\Grado;
use Illuminate\Http\Request;

class SeccionesController extends CrudControllerBase
{
    protected function configure(Request $request): void
    {
        $this->model(Seccion::class);

        // Opciones para selects
        $gradoOpts = Grado::orderBy('nombre')->pluck('nombre', 'id')->toArray();

        // Solo empleados cuyo user tiene rol Docente (rol_id=4), ordenados por nombre
        $docentesOpts = Empleado::select('empleados.*')
            ->join('users', 'users.id', '=', 'empleados.usuario_id')
            ->where('users.rol_id', 4) // o Role::where('nombre','Docente')->value('id')
            ->orderBy('users.name')
            ->orderBy('users.apellido')
            ->with('user')
            ->get()
            ->mapWithKeys(fn ($e) => [$e->id => $e->nombre_completo])
            ->toArray();

        $alumnosOpts = Estudiante::select('estudiantes.id','users.name','users.apellido')
            ->join('users','users.id','=','estudiantes.usuario_id')
            ->orderBy('users.name')->orderBy('users.apellido')
            ->get()
            ->mapWithKeys(fn($r) => [$r->id => trim($r->name.' '.$r->apellido)])
            ->toArray();

        $this->column('id')
            ->label('ID')
            ->readonly();

        $this->column('estudiantes_lista_short')
            ->label('Estudiantes')
            ->readonly();

        // Campos reales del form
        $this->column('maestro_guia_id')
            ->label('Maestro guía')
            ->type('relation')
            ->options($docentesOpts)
            ->filterable('select')
            ->filterOptions($docentesOpts)
            ->rules(['required', 'exists:empleados,id']);

        $this->column('grado_id')
            ->label('Grado')
            ->type('relation')
            ->options($gradoOpts)
            ->filterable('select')
            ->filterOptions($gradoOpts)
            ->rules(['required', 'exists:grado,id']);

        $this->column('seccion')
            ->label('Sección')
            ->rules(['required', 'string', 'max:255']);

        $this->column('estudiantes_id')
            ->label('Estudiantes')
            ->type('relation')
            ->options($alumnosOpts)
            ->filterable('select')
            ->filterOptions($alumnosOpts)
            ->rules(['array', 'exists:estudiantes,id'])
            ->pivot('estudiantes')   // <- nombre del método en el modelo Seccion
            ->multiRelation()
            ->hide();

        $this->syncAbilities('admin.secciones');
    }
}