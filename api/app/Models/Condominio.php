<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Condominio extends Model
{
    protected $table = 'condominios';
    protected $primaryKey = 'id_condominio';
    public $timestamps = false;

    protected $fillable = ['nombre', 'tipo', 'direccion', 'telefono'];

    public function unidades(): HasMany
    {
        return $this->hasMany(Unidad::class, 'id_condominio', 'id_condominio');
    }
}
