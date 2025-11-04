<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    protected $with = ['user'];

    protected $appends = ['nombre_completo', 'rol_nombre', 'creado_en'];

    /**
     * Relación con el usuario dueño del empleado.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id')
            ->withDefault([
                'name' => '',
                'apellido' => '',
            ]);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagosEmpleado::class, 'empleado_id');
    }

    public function scopeDocentes($q, $rolDocenteId = 4)
    {
        return $q->whereHas('user', fn ($u) => $u->where('rol_id', $rolDocenteId));
    }

    public function getNombreCompletoAttribute(): string
    {
        $nombre = $this->user->name ?? '';
        $apellido = $this->user->apellido ?? '';
        return trim($nombre . ' ' . $apellido);
    }

    public function getRolNombreAttribute(): string
    {
        return $this->user?->role?->nombre ?? '';
    }

    public function getCreadoEnAttribute(): string
    {
        return $this->created_at
            ? $this->created_at->format('d/m/Y H:i')
            : '';
    }
}
