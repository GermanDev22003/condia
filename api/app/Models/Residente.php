<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Residente extends Model
{
    protected $table = 'residentes';
    protected $primaryKey = 'id_residente';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario', 'id_unidad', 'nombres', 'apellidos',
        'telefono', 'fecha_ingreso', 'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date:Y-m-d',
        ];
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class, 'id_unidad', 'id_unidad');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
