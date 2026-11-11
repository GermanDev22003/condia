<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cuota extends Model
{
    protected $table = 'cuotas';
    protected $primaryKey = 'id_cuota';
    public $timestamps = false;

    protected $fillable = ['id_residente', 'concepto', 'monto', 'periodo', 'fecha_vencimiento', 'estado'];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha_vencimiento' => 'date:Y-m-d',
        ];
    }

    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'id_residente', 'id_residente');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'id_cuota', 'id_cuota');
    }

    /** Las cuotas pendientes con fecha de vencimiento pasada se marcan como vencidas. */
    public static function marcarVencidas(): void
    {
        static::where('estado', 'pendiente')
            ->whereDate('fecha_vencimiento', '<', now()->toDateString())
            ->update(['estado' => 'vencida']);
    }

    /** Suma de lo pagado hasta ahora. */
    public function totalPagado(): string
    {
        return number_format((float) $this->pagos()->sum('monto_pagado'), 2, '.', '');
    }
}
