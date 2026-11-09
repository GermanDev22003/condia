<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Queja extends Model
{
    protected $table = 'quejas';
    protected $primaryKey = 'id_queja';
    public $timestamps = false;

    public const CATEGORIAS = ['convivencia', 'limpieza', 'seguridad', 'ruido', 'infraestructura', 'otra'];

    protected $fillable = ['id_residente', 'asunto', 'categoria', 'descripcion', 'estado', 'respuesta'];

    protected function casts(): array
    {
        return ['fecha_registro' => 'datetime:Y-m-d H:i'];
    }

    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'id_residente', 'id_residente');
    }
}
