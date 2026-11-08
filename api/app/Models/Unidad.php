<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unidad extends Model
{
    protected $table = 'unidades';
    protected $primaryKey = 'id_unidad';
    public $timestamps = false;

    protected $fillable = ['id_condominio', 'numero', 'torre_bloque', 'nivel'];

    public function condominio(): BelongsTo
    {
        return $this->belongsTo(Condominio::class, 'id_condominio', 'id_condominio');
    }

    public function residentes(): HasMany
    {
        return $this->hasMany(Residente::class, 'id_unidad', 'id_unidad');
    }
}
