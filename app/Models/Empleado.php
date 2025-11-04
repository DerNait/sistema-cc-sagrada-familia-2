<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $nombre_completo
 * @property-read string $rol_nombre
 */
class Empleado extends Model
{
    protected $table = 'empleados';

    public $timestamps = true;

    protected $fillable = [
        'usuario_id',
        'salario_base',
        'created_at',
        'updated_at',
    ];

    // Evita N+1 al leer pagos con el usuario; quítalo si no lo deseas
    protected $with = ['user'];

    protected $appends = ['nombre_completo', 'rol_nombre'];

    /**
     * Relación con el usuario dueño del empleado.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id')
            ->withDefault([
                // Si tu tabla 'usuarios' tiene columnas 'nombre' y 'apellido':
                'nombre'   => '',
                'apellido' => '',
                // Si usas 'name' en lugar de 'nombre', podrías usar:
                // 'name' => '',
            ]);
    }

    /**
     * Pagos del empleado.
     * Ojo con el nombre del modelo: usa el que realmente tengas (PagosEmpleado vs EmpleadoPago).
     */
    public function pagos(): HasMany
    {
        // Si tu modelo se llama PagosEmpleado:
        return $this->hasMany(PagosEmpleado::class, 'empleado_id');

        // Si tu modelo se llama EmpleadoPago, usa esta línea en su lugar:
        // return $this->hasMany(EmpleadoPago::class, 'empleado_id');
    }

    /**
     * Scope para filtrar docentes por rol en usuarios.
     */
    public function scopeDocentes($q, $rolDocenteId = 4)
    {
        return $q->whereHas('user', fn ($u) => $u->where('rol_id', $rolDocenteId));
    }

    /**
     * Accessor nombre completo coherente con tu migración de 'usuarios'.
     */
    public function getNombreCompletoAttribute(): string
    {
        // Si tu User tiene 'nombre' y 'apellido' (según tu migración de 'usuarios'):
        $nombre = $this->user->nombre ?? '';
        $apellido = $this->user->apellido ?? '';

        // Si tu User en realidad usa 'name' y no 'nombre', descomenta:
        // $nombre = $nombre ?: ($this->user->name ?? '');

        return trim($nombre . ' ' . $apellido);
    }

    /**
     * Accessor para el nombre del rol (relación user->role->nombre).
     */
    public function getRolNombreAttribute(): string
    {
        return $this->user?->role?->nombre ?? '';
    }
}
