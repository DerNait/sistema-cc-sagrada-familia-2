<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    protected $appends = ['nombre_completo', 'rol_nombre', 'creado_en', 'estado_pago_mes_actual'];

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

    public function role(): BelongsTo
    {
        return $this->user->role();
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagosEmpleado::class, 'empleado_id');
    }

    /**
     * Relación al pago correspondiente al mes/año actual (si existe).
     */
    public function pagoMesActual(): HasOne
    {
        // Se filtra por created_at del pago en el mes/año actual.
        return $this->hasOne(PagosEmpleado::class, 'empleado_id')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);
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

    /**
     * Estado del pago para el mes/año actual: Cancelado, Completado o Pendiente.
     */
    public function getEstadoPagoMesActualAttribute(): string
    {
        $pago = $this->pagos()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->orderByDesc('id')
            ->first();

        if (! $pago) {
            return 'Pendiente';
        }

        // Si el registro de pago tiene un campo 'estado', intentar mapearlo.
        if (isset($pago->estado)) {
            $estado = mb_strtolower((string) $pago->estado);
            if (in_array($estado, ['cancelado', 'pagado', 'paid'], true)) {
                return 'Cancelado';
            }
            if (in_array($estado, ['completado', 'completed'], true)) {
                return 'Completado';
            }
            if (in_array($estado, ['pendiente', 'pending'], true)) {
                return 'Pendiente';
            }
        }

        // Si no hay campo 'estado', inferir por montos si están disponibles.
        if (isset($pago->monto) && isset($this->salario_base)) {
            $monto = (float) $pago->monto;
            $salario = (float) $this->salario_base;
            if ($monto >= $salario) {
                return 'Cancelado';
            }
            if ($monto > 0) {
                return 'Completado';
            }
        }

        return 'Pendiente';
    }

    
}
