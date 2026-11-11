<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudMantenimiento extends Model
{
    protected $table = 'solicitudes_mantenimiento';
    protected $primaryKey = 'id_solicitud';
    public $timestamps = false;

    protected $fillable = ['id_residente', 'id_personal', 'titulo', 'ubicacion', 'prioridad', 'estado', 'descripcion'];

    protected function casts(): array
    {
        return ['fecha_registro' => 'datetime:Y-m-d H:i'];
    }

    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'id_residente', 'id_residente');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'id_personal', 'id_personal');
    }
}
