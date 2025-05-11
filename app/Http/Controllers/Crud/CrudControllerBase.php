<?php
namespace App\Http\Controllers\Crud;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Support\Forerunner\Forerunner;

abstract class CrudControllerBase extends Controller
{
    /** @var class-string<Model>|null */
    protected ?string $modelClass = null;

    /** Config de columnas (ver setColumn) */
    protected array $columns = [];

    /** Permisos calculados en runtime */
    protected array $abilities = [];

    /* ===============================================================
     *  API pública que la clase hija DEBE implementar / llamar
     * ============================================================= */

    /** Punto de configuración; la hija lo implementa */
    abstract protected function configure(Request $request): void;

    /** Helper para definir el modelo */
    protected function model(string $class): void
    {
        $this->modelClass = $class;
    }

    /** Helper para registrar columnas
     *  Example: $this->column('nombre')->label('Nombre')->type('string')->editable()
     */
    protected function column(string $field): ColumnConfig
    {
        return $this->columns[$field] = $this->columns[$field] ?? new ColumnConfig($field);
    }

    /* ===============================================================
     *  Rutas CRUD genéricas
     * ============================================================= */
    public function index(Request $request)
    {
        $this->configure($request);

        // 1) campos y relaciones como antes...
        $all   = array_keys($this->columns);
        $rels  = []; $locals = [];
        foreach ($all as $f) {
            if (str_contains($f,'.')) {
                $rels[] = explode('.', $f, 2)[0];
            } else {
                $locals[] = $f;
            }
        }

        // 2) removemos el select()
        $query = $this->newModelQuery()
                    ->with(array_unique($rels));

        // 3) búsqueda, paginación…
        if ($q = $request->query('q')) {
            $query->where(function($w) use($q,$locals){
                foreach ($locals as $c) {
                    $w->orWhere($c,'ILIKE',"%{$q}%");
                }
            });
        }

        $data = $query->paginate($request->query('per_page',20));

        return view('crud.table', [
        'data'      => $data,
        'columns'   => $this->columns,
        'abilities' => $this->abilities,
        ]);
    }

    public function create(Request $request)
    {
        $this->configure($request);
        abort_unless($this->abilities['create'] ?? false, 403);

        return view('crud.form', [
            'item'     => null,
            'columns'  => $this->columns,
            'action'  => route(Str::beforeLast($request->route()->getName(), '.') . '.store'),
        ]);
    }

    public function store(Request $request)
    {
        $this->configure($request);
        abort_unless($this->abilities['create'] ?? false, 403);

        $data = $this->validatedData($request);
        $item = $this->newModelQuery()->create($data);

        $full   = $request->route()->getName();
        $base   = Str::beforeLast($full, '.');

        return redirect()
            ->route("{$base}.index")
            ->with('success', 'Registro creado con éxito');
    }

    public function edit(Request $request, $id)
    {
        $this->configure($request);
        abort_unless($this->abilities['update'] ?? false, 403);

        $item = $this->newModelQuery()->findOrFail($id);

        return view('crud.form', [
            'item'    => $item,
            'columns' => $this->columns,
            'action'  => route(Str::beforeLast($request->route()->getName(), '.') . '.update', $item->getKey()),
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->configure($request);
        abort_unless($this->abilities['update'] ?? false, 403);

        $item = $this->newModelQuery()->findOrFail($id);
        $item->update($this->validatedData($request));

        $full   = $request->route()->getName();
        $base   = Str::beforeLast($full, '.');

        return redirect()
            ->route("{$base}.index")
            ->with('success', 'Registro actualizado con éxito');
    }

    public function destroy(Request $request, $id)
    {
        $this->configure($request);
        abort_unless($this->abilities['delete'] ?? false, 403);

        $this->newModelQuery()->findOrFail($id)->delete();
        return back()->with('warning', 'Registro eliminado');
    }

    /* ===============================================================
     *  Helpers internos
     * ============================================================= */

    protected function newModelInstance(): Model
    {
        return app($this->modelClass);
    }

    protected function newModelQuery()
    {
        return $this->newModelInstance()->newQuery();
    }

    /** Valida según reglas propias de cada columna */
    protected function validatedData(Request $request): array
    {
        $rules = collect($this->columns)
            ->filter(fn ($col) => $col->rules)
            ->mapWithKeys(fn ($col) => [$col->field => $col->rules])
            ->toArray();

        return $request->validate($rules);
    }

    /** Debe llamarse al final de configure(): calcula permisos CRUD  */
    protected function syncAbilities(string $moduleName): void
    {
        $this->abilities = Forerunner::crudMatrix($moduleName);
    }
}

/* ---------------------------------------------------------------
 *  Configurador fluido de columnas
 * ------------------------------------------------------------- */
class ColumnConfig
{
    public string $field;
    public string $label;
    public string $type      = 'string'; // string|numeric|datetime|relation…
    public bool   $visible   = true;
    public bool   $editable  = true;
    public array  $rules     = [];
    public ?array $options   = null;

    public function __construct(string $field)
    {
        $this->field = $field;
        $this->label = ucfirst(str_replace('_', ' ', $field));
    }

    public function label(string $label): self   { $this->label = $label;   return $this; }
    public function type(string $type): self     { $this->type  = $type;     return $this; }
    public function hide(): self                 { $this->visible = false;   return $this; }
    public function readonly(): self             { $this->editable = false;  return $this; }
    public function rules(array $rules): self    { $this->rules = $rules;    return $this; }
    public function options(array $opts): self   { $this->options = $opts;   return $this; }
}