<?php

namespace App\Http\Controllers\Catalogs;

use App\Http\Controllers\Crud\CrudControllerBase;
use App\Models\Curso;
use App\Models\Grado;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

        $this->syncAbilities('admin.cursos');
    }

    /**
     * Validación personalizada antes de guardar
     */
    protected function beforeStore(Request $request, $data)
    {
        // Limpiar el nombre: eliminar espacios extra y trim
        $data['nombre'] = $this->limpiarNombre($request->input('nombre'));
        
        $this->validateUniqueNombre($data['nombre']);
        return $data;
    }

    /**
     * Validación personalizada antes de actualizar
     */
    protected function beforeUpdate(Request $request, $item, $data)
    {
        // Limpiar el nombre: eliminar espacios extra y trim
        $data['nombre'] = $this->limpiarNombre($request->input('nombre'));
        
        $this->validateUniqueNombre($data['nombre'], $item->id);
        return $data;
    }

    /**
     * Limpia el nombre eliminando espacios extra y haciendo trim
     */
    private function limpiarNombre($nombre)
    {
        return preg_replace('/\s+/', ' ', trim($nombre));
    }

    /**
     * Valida que el nombre del curso sea único (case-insensitive y sin espacios)
     */
    private function validateUniqueNombre($nombre, $ignoreId = null)
    {
        // Eliminar TODOS los espacios y convertir a minúsculas para comparar
        $nombreSinEspacios = strtolower(str_replace(' ', '', $nombre));
        
        // Obtener todos los cursos excepto el actual
        $query = Curso::query();
        
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }
        
        // Verificar si existe algún curso con el mismo nombre (sin espacios)
        $existe = $query->get()->contains(function ($curso) use ($nombreSinEspacios) {
            $nombreExistente = strtolower(str_replace(' ', '', $curso->nombre));
            return $nombreExistente === $nombreSinEspacios;
        });
        
        if ($existe) {
            throw ValidationException::withMessages([
                'nombre' => ['Ya existe un curso con este nombre. Por favor, elige un nombre diferente.']
            ]);
        }
    }
}
