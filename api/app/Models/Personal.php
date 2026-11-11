<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    protected $table = 'personal';
    protected $primaryKey = 'id_personal';
    public $timestamps = false;

    protected $fillable = ['id_condominio', 'id_usuario', 'nombres', 'apellidos', 'cargo', 'turno', 'telefono', 'estado'];
}
