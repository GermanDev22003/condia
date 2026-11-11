<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla auxiliar de la API: guarda los tokens de inicio de sesion (solo el hash).
        Schema::create('tokens_acceso', function (Blueprint $table) {
            $table->increments('id_token');
            $table->unsignedInteger('id_usuario');
            $table->char('token_hash', 64)->unique();
            $table->dateTime('ultimo_uso')->nullable();
            $table->dateTime('creado_en')->useCurrent();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tokens_acceso');
    }
};
