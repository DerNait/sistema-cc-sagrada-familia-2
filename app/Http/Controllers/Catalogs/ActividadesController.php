<?php

namespace App\Http\Controllers\Catalogs;

use Illuminate\Http\Request;
use App\Models\Actividad;
use App\Models\Grado;
use App\Models\Curso;
use App\Models\GradoCurso;
use App\Http\Controllers\Crud\CrudControllerBase;
use Illuminate\Support\Facades\DB;

class ActividadesController extends CrudControllerBase
{
    protected function configure(Request $request): void
    {
        $this->model(Actividad::class);

        /*─ columnas comunes ─*/
        $this->column('id')->label('ID')->readonly();

        $this->column('nombre')
            ->label('Nombre')
            ->rules(['required','string','max:255']);

        /* -----   selects dependientes   ----- */
        $gradoOpts = Grado::orderBy('nombre')->pluck('nombre','id')->toArray();
        $cursoOpts = Curso::orderBy('nombre')->pluck('nombre','id')->toArray();

        $this->column('grado_name')
            ->label('Grado')
            ->readonly();

        $this->column('curso_name')
            ->label('Curso')
            ->readonly();

        $this->column('grado_id')
            ->label('Grado')
            ->type('relation')           // <select>
            ->options($gradoOpts)
            ->filterable('select')
            ->filterOptions($gradoOpts)
            ->rules(['required','exists:grado,id'])
            ->hide();

        $this->column('curso_id')
            ->label('Curso')
            ->type('relation')
            ->options($cursoOpts)
            ->filterable('select')
            ->filterOptions($cursoOpts)
            ->rules(['required','exists:cursos,id'])
            ->hide();

        /*   ocultamos la FK real   */
        $this->column('grado_curso_id')
            ->hide()
            ->readonly();

        /* fechas y puntaje */
        $this->column('fecha_inicio')->type('date')->rules(['required','date']);
        $this->column('fecha_fin')->type('date')->rules(['required','date','after:fecha_inicio']);
        $this->column('total')->type('numeric')->rules(['required','integer','min:1']);

        $this->column('created_at')->label('Creado')->type('datetime')->readonly()->hide();
        $this->column('updated_at')->label('Actualizado')->type('datetime')->readonly()->hide();

        $this->syncAbilities('actividades');
    }

    /* ---------- override store ---------- */
    public function store(Request $request)
    {
        $this->configure($request);
        abort_unless($this->abilities['create'] ?? false, 403);

        $data = $this->validatedData($request);

        $data['grado_curso_id'] = GradoCurso::firstOrCreate([
            'grado_id' => $data['grado_id'],
            'curso_id' => $data['curso_id'],
        ])->id;

        unset($data['grado_id'], $data['curso_id']);

        $item = DB::transaction(fn() => Actividad::create($data));

        return $item->refresh();
    }

    /* ---------- override update ---------- */
    public function update(Request $request, $id)
    {
        $this->configure($request);
        abort_unless($this->abilities['update'] ?? false, 403);

        $item = Actividad::findOrFail($id);
        $data = $this->validatedData($request);

        $data['grado_curso_id'] = GradoCurso::firstOrCreate([
            'grado_id' => $data['grado_id'],
            'curso_id' => $data['curso_id'],
        ])->id;

        unset($data['grado_id'], $data['curso_id']);

        DB::transaction(fn() => $item->update($data));

        return $item->refresh();
    }
}
