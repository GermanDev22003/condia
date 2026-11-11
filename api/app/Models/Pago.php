<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $table = 'pagos';
    protected $primaryKey = 'id_pago';
    public $timestamps = false;

    protected $fillable = ['id_cuota', 'id_usuario_registro', 'monto_pagado', 'fecha_pago', 'metodo_pago', 'referencia'];

    protected function casts(): array
    {
        return [
            'monto_pagado' => 'decimal:2',
            'fecha_pago' => 'date:Y-m-d',
        ];
    }

    public function cuota(): BelongsTo
    {
        return $this->belongsTo(Cuota::class, 'id_cuota', 'id_cuota');
    }
}
