<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenAcceso extends Model
{
    protected $table = 'tokens_acceso';
    protected $primaryKey = 'id_token';
    public $timestamps = false;

    protected $fillable = ['id_usuario', 'token_hash', 'ultimo_uso'];

    protected function casts(): array
    {
        return [
            'ultimo_uso' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
