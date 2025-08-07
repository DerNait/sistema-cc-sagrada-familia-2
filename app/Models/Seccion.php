<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
    protected $table = 'secciones';

    protected $fillable = ['maestro_guia_id', 'grado_id', 'seccion'];

    public function maestroGuia()
    {
        return $this->belongsTo(User::class, 'maestro_guia_id');
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
}