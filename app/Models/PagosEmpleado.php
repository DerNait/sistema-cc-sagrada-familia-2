<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PagosEmpleado extends Model
{
    protected $table = 'pagos_empleados';

    protected $guarded = ['id'];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'aprobado_en'   => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /* ==========================
     |  Relaciones
     ========================== */

    /**
     * Empleado asociado a este pago
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    /**
     * Estado del pago (Pendiente, Pagado, Cancelado, etc.)
     */
    public function tipoEstado(): BelongsTo
    {
        return $this->belongsTo(TipoEstado::class);
    }

    /**
     * Usuario que aprobó el pago (admin/secretaria)
     */
    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_id');
    }

    /* ==========================
     |  Accessors / Helpers
     ========================== */

    /**
     * Fecha de ingreso formateada
     */
    public function getFechaIngresoLabelAttribute(): ?string
    {
        return $this->fecha_ingreso
            ? Carbon::parse($this->fecha_ingreso)->locale('es')->isoFormat('D [de] MMMM [de] YYYY')
            : null;
    }

    /**
     * Nombre completo del empleado (con fallback)
     */
    public function getEmpleadoNombreAttribute(): string
    {
        $user = $this->empleado->user ?? null;
        if (!$user) return 'N/A';
        return trim(($user->name ?? '') . ' ' . ($user->apellido ?? '')) ?: 'N/A';
    }

    /**
     * Determina si el pago está marcado como pagado
     */
    public function getEstaPagadoAttribute(): bool
    {
        return optional($this->tipoEstado)->tipo === 'Completado';
    }

    /**
     * Determina si el pago está pendiente
     */
    public function getEstaPendienteAttribute(): bool
    {
        return optional($this->tipoEstado)->tipo === 'Pendiente';
    }

    /**
     * Determina si el pago fue cancelado
     */
    public function getEstaCanceladoAttribute(): bool
    {
        return optional($this->tipoEstado)->tipo === 'Cancelado';
    }

    /* ==========================
     |  Scopes personalizados
     ========================== */

    /**
     * Filtrar pagos del año y mes específicos
     */
    public function scopeDelPeriodo($q, int $anio, int $mes)
    {
        return $q->where('periodo_anio', $anio)->where('periodo_mes', $mes);
    }

    /**
     * Filtrar pagos de un empleado
     */
    public function scopeDelEmpleado($q, int $empleadoId)
    {
        return $q->where('empleado_id', $empleadoId);
    }

    /* ==========================
     |  Helpers de totales
     ========================== */

    /**
     * Calcular total con bonificaciones y descuentos
     */
    public static function calcularTotal(array $data): float
    {
        return
            ($data['salario_base'] ?? 0) +
            ($data['bonificacion_ley'] ?? 0) +
            ($data['bonificacion_extra'] ?? 0) -
            ($data['descuento_igss'] ?? 0) -
            ($data['descuentos_varios'] ?? 0);
    }
}
