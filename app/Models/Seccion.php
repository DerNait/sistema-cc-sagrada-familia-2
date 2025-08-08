<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
    protected $table = 'secciones';

    protected $fillable = ['maestro_guia_id', 'grado_id', 'seccion'];

    protected $appends = ['estudiantes_lista', 'estudiantes_id', 'estudiantes_lista_short'];

    public function maestroGuia()
    {
        return $this->belongsTo(Empleado::class, 'maestro_guia_id');
    }

    public function grado()
    {
        return $this->belongsTo(Grado::class);
    }

    public function estudiantes()
    {
        // ⚠️ especifica la tabla pivote + claves:
        return $this->belongsToMany(
            Estudiante::class,
            'seccion_estudiantes',   // ← plural
            'seccion_id',
            'estudiante_id'
        );
    }

    public function cursos()
    {
        return $this->hasMany(Curso::class); 
    }

    public function getEstudiantesListaAttribute(): string
    {
        $this->loadMissing(['estudiantes.usuario:id,name,apellido']);
        return $this->estudiantes
            ->map(fn($e) => trim(($e->usuario->name ?? '') . ' ' . ($e->usuario->apellido ?? '')))
            ->filter()
            ->implode(', ');
    }

    public function getEstudiantesIdAttribute(): array
    {
        $this->loadMissing('estudiantes:id');
        return $this->estudiantes->pluck('id')->toArray();
    }

    public function getEstudiantesListaShortAttribute(): string
    {
        // solo 3 para mostrar
        $firstThree = $this->estudiantes()
            ->with('usuario:id,name,apellido')
            ->limit(3)
            ->get();

        $names = $firstThree
            ->map(fn($e) => trim(($e->usuario->name ?? '') . ' ' . ($e->usuario->apellido ?? '')))
            ->filter();

        // cuenta total sin traerlos todos
        $this->loadCount('estudiantes');
        $rest = max(0, ($this->estudiantes_count ?? 0) - $names->count());

        return $rest > 0
            ? $names->implode(', ') . " … (+{$rest})"
            : $names->implode(', ');
    }
}