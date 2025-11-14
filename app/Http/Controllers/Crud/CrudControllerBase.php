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

    /**  Acciones extra por fila */
    protected array $customActions = [];

    /**  Acciones globales del módulo (no por fila) */
    protected array $globalActions = []; 

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

    protected function action(string $name): ActionConfigRow
    {
        return $this->customActions[$name] =
            $this->customActions[$name] ?? new ActionConfigRow($name);
    }

    /** Registrar acciones globales (misma API fluida que ActionConfigRow) */
    protected function globalAction(string $name): GlobalActionConfig
    {
        return $this->globalActions[$name] =
            $this->globalActions[$name] ?? new GlobalActionConfig($name);
    }

    /* ===============================================================
     *  Rutas CRUD genéricas
     * ============================================================= */
    public function index(Request $request)
    {
        $this->configure($request);

        // 1) Separamos campos locales vs relaciones (todas las rutas, no sólo el primer nivel)
        $all       = array_keys($this->columns);
        $localCols = [];
        $relationPaths = [];

        foreach ($all as $field) {
            if (str_contains($field, '.')) {
                // por ejemplo 'user.role.nombre' → querremos eager-load de 'user.role'
                $relationPaths[] = Str::beforeLast($field, '.');
            } else {
                $localCols[] = $field;
            }
        }

        // 2) Armamos la query con eager-load de todas las relaciones
        $query = $this->newModelQuery()
                    ->with(array_unique($relationPaths));

        // 3) Aplicamos filtros dinámicos, incluyendo relaciones anidadas
        foreach ($this->columns as $col) {
            if (! $col->filterable) {
                continue;
            }

            // Recupera el valor, soportando nested arrays ó dot notation
            $value = Arr::get($request->query(), $col->field);

            if ($value === null || $value === '') {
                continue;
            }

            if (str_contains($col->field, '.')) {
                // ej: 'user.role.id'
                $parts = explode('.', $col->field);
                $attr  = array_pop($parts);   // 'id'
                $root  = array_shift($parts); // 'user'
                $rest  = $parts;              // ['role']

                // construimos whereHas anidado dinámicamente
                $query->whereHas($root, function($q) use ($rest, $attr, $value) {
                    // si hay más niveles, encadenamos whereHas
                    if (count($rest) > 0) {
                        $next = array_shift($rest);
                        $q->whereHas($next, function($q2) use ($rest, $attr, $value) {
                            if (count($rest) > 0) {
                                // en caso extremo de 3+ niveles
                                $this->applyNestedWhereHas($q2, $rest, $attr, $value);
                            } else {
                                $q2->where($attr, $value);
                            }
                        });
                    } else {
                        // sólo un nivel (no debería pasar, pues . fue detectado)
                        $q->where($attr, $value);
                    }
                });

            } else {
                // filtro local
                switch ($col->filterType) {
                    case 'select':
                    case 'numeric':
                        $query->where($col->field, $value);
                        break;
                    case 'date':
                        $query->whereDate($col->field, $value);
                        break;
                    default:
                        $query->where($col->field, 'ILIKE', "%{$value}%");
                }
            }
        }

        // 4) Búsqueda global sobre columnas locales
        if ($q = $request->query('q')) {
            $query->where(function($w) use($q, $localCols) {
                foreach ($localCols as $c) {
                    $w->orWhere($c, 'ILIKE', "%{$q}%");
                }
            });
        }

        // 5) Obtenemos toda la data
        $data = $query->get();

            
        // 6) Enviamos todo a la vista
        $params = [
            'data'           => $data,
            'columns'        => $this->columns,
            'abilities'      => $this->abilities,
            'custom_actions' => array_values($this->customActions),
            'global_actions' => array_values($this->globalActions), 
            'filters'        => $request->only(
                array_filter($all, fn($f) => $this->columns[$f]->filterable)
            ),
        ];

        return view('component', [
            'component' => 'crud-table',
            'params'    => $params
        ]);
    }

    public function show(Request $request, $id)
    {
        $this->configure($request);

        abort_unless($this->abilities['read'] ?? true, 403);

        return $this->newModelQuery()->findOrFail($id);
    }

    public function create(Request $request)
    {
        $this->configure($request);
        abort_unless($this->abilities['create'] ?? false, 403);

        $params = [
            'item'     => null,
            'columns'  => $this->columns,
            'action'  => route(Str::beforeLast($request->route()->getName(), '.') . '.store'),
        ];

        return view('component', [
            'component' => 'crud-form',
            'params'    => $params
        ]);
    }

    public function store(Request $request)
    {
        $this->configure($request);
        abort_unless($this->abilities['create'] ?? false, 403);

        DB::transaction(function () use ($request, &$item) {
            $data = $this->validatedData($request);
            
            // Hook para validaciones adicionales antes de crear
            if (method_exists($this, 'beforeStore')) {
                $data = $this->beforeStore($request, $data);
            }
            
            $item = $this->newModelQuery()->create($data);
            $this->syncPivotRelations($request, $item);
        });

        return $item->refresh();
    }

    public function edit(Request $request, $id)
    {
        $this->configure($request);
        abort_unless($this->abilities['update'] ?? false, 403);

        $item = $this->newModelQuery()->findOrFail($id);

        $params = [
            'item'    => $item,
            'columns' => $this->columns,
            'action'  => route(Str::beforeLast($request->route()->getName(), '.') . '.update', $item->getKey()),
        ];

        return view('component', [
            'component' => 'crud-form',
            'params'    => $params
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->configure($request);
        abort_unless($this->abilities['update'] ?? false, 403);

        $item = $this->newModelQuery()->findOrFail($id);

        DB::transaction(function () use ($request, $item) {
            $data = $this->validatedData($request);
            
            // Hook para validaciones adicionales antes de actualizar
            if (method_exists($this, 'beforeUpdate')) {
                $data = $this->beforeUpdate($request, $item, $data);
            }
            
            $item->update($data);
            $this->syncPivotRelations($request, $item);
        });

        return $item->refresh();
    }

    public function destroy(Request $request, $id)
    {
        $this->configure($request);
        abort_unless($this->abilities['delete'] ?? false, 403);

        $this->newModelQuery()->findOrFail($id)->delete();

        return response()->json(['status' => 'ok']);
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

    /**
     * Helper recursivo para whereHas de 3+ niveles.
     */
    protected function applyNestedWhereHas($query, array $relations, string $attr, $value)
    {
        $next = array_shift($relations);
        $query->whereHas($next, function($q) use ($relations, $attr, $value) {
            if (count($relations) > 0) {
                $this->applyNestedWhereHas($q, $relations, $attr, $value);
            } else {
                $q->where($attr, $value);
            }
        });
    }

    /** Sincroniza belongsToMany que lo pidan */
    protected function syncPivotRelations(Request $req, Model $item): void
    {
        foreach ($this->columns as $col) {
            if ($col->pivotRelation) {
                $ids = $req->input($col->field, []);
                $item->{$col->pivotRelation}()->sync($ids);
            }
        }
    }

    /* ---------------------------------------------------------------
    *  Funcion de exportar archivos csv
    * ------------------------------------------------------------- */
    public function export(Request $request)
    {
        $this->configure($request);
        abort_unless($this->abilities['read'] ?? true, 403);

        // Obtener todos los datos sin paginación
        $data = $this->newModelQuery()->get();

        // Filtrar solo las columnas visibles
        $visibleColumns = array_filter($this->columns, fn($col) => $col->visible);
        
        // Generar contenido CSV
        $csvContent = $this->generateCsv($data, $visibleColumns);

        // Configurar respuesta de descarga
        $modelName = class_basename($this->modelClass);
        $filename = strtolower($modelName) . '_export_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(
            fn() => print($csvContent),
            $filename,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }

    protected function generateCsv($data, $columns): string
    {
        $output = fopen('php://temp', 'r+');
        
        // Escribir encabezados
        fputcsv($output, array_map(fn($col) => $col->label, $columns));
        
        // Escribir datos
        foreach ($data as $item) {
            $row = [];
            foreach ($columns as $field => $column) {
                $row[] = $this->getExportValue($item, $field);
            }
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    protected function getExportValue($item, $field)
    {
        if (str_contains($field, '.')) {
            // Para relaciones anidadas
            return data_get($item, $field) ?? '';
        }
        
        return $item->{$field} ?? '';
    }
}

/* ---------------------------------------------------------------
 *  Configurador fluido de columnas
 * ------------------------------------------------------------- */
class ColumnConfig
{
    public string $field;
    public string $label;
    public string $type           = 'string'; // string|numeric|datetime|relation…
    public bool   $visible        = true;
    public bool   $editable       = true;
    public array  $rules          = [];
    public ?array $options        = null;
    public ?string $pivotRelation = null;
    public bool $isMultiRelation = false;

    public function __construct(string $field)
    {
        $this->field = $field;
        $this->label = ucfirst(str_replace('_', ' ', $field));
    }

    public function label(string $label): self             { $this->label = $label;   return $this; }
    public function type(string $type): self               { $this->type  = $type;     return $this; }
    public function hide(): self                           { $this->visible = false;   return $this; }
    public function readonly(): self                       { $this->editable = false;  return $this; }
    public function rules(array $rules): self              { $this->rules = $rules;    return $this; }
    public function options(array $opts): self             { $this->options = $opts;   return $this; }
    public function pivot(string $relation): self          { $this->pivotRelation = $relation; return $this; }
    public function multiRelation(bool $flag = true): self { $this->isMultiRelation = $flag; return $this; }

    public bool   $filterable    = false;
    public string $filterType    = 'text';   // 'text'|'select'|'date'|'numeric'
    public ?array $filterOptions = null;

    public function filterable(string $type = 'text'): self
    {
        $this->filterable = true;
        $this->filterType = $type;
        return $this;
    }

    public function filterOptions(array $opts): self
    {
        $this->filterOptions = $opts;
        return $this;
    }
}

/** Configurador fluido de acciones (solo usa string con __ID__) */
class ActionConfigRow
{
    public string  $name;
    public string  $label    = '';
    public string  $icon     = 'fa-asterisk';
    public string  $btnClass = 'btn-outline-secondary';

    /**
     * String con el marcador __ID__ que será reemplazado en el front
     *   p.ej. '/usuarios/__ID__/reset-password'
     */
    public ?string $url = null;

    /** Habilidad necesaria para mostrar el botón (read, update, delete, …) */
    public ?string $ability = null;

    public function __construct(string $name) { $this->name = $name; }

    public function label(string $t): self   { $this->label    = $t;  return $this; }
    public function icon(string $fa): self   { $this->icon     = $fa; return $this; }
    public function btn(string $cls): self   { $this->btnClass = $cls;return $this; }
    public function url(string $u): self     { $this->url      = $u;  return $this; }
    public function ability(string $a): self { $this->ability  = $a;  return $this; }
}

class GlobalActionConfig
{
    public string  $name;
    public string  $label    = '';
    public string  $icon     = 'fa-asterisk';
    public string  $btnClass = 'btn-outline-secondary';

    public ?string $url = null;
    public ?string $ability = null;

    public string $method = 'GET';              // GET | POST | PUT | PATCH | DELETE
    public string $confirmMode = 'none';        // none | alert | confirm
    public string $confirmText = '';
    public array  $payload = [];                // datos extra para requests no-GET

    public function __construct(string $name) { $this->name = $name; }

    public function label(string $t): self   { $this->label    = $t;  return $this; }
    public function icon(string $fa): self   { $this->icon     = $fa; return $this; }
    public function btn(string $cls): self   { $this->btnClass = $cls;return $this; }
    public function url(string $u): self     { $this->url      = $u;  return $this; }
    public function ability(string $a): self { $this->ability  = $a;  return $this; }

    public function method(string $m): self
    {
        $this->method = strtoupper($m);
        return $this;
    }

    public function confirm(string $mode = 'confirm', string $text = ''): self
    {
        $this->confirmMode = $mode;
        $this->confirmText = $text;
        return $this;
    }

    public function payload(array $data): self
    {
        $this->payload = $data;
        return $this;
    }
}