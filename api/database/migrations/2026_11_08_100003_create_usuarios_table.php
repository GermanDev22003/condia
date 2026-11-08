<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('id_usuario');
            $table->string('correo', 150)->unique();
            $table->string('password', 255);
            $table->string('rol', 30);
            $table->boolean('activo')->default(true);
        });

        DB::statement("ALTER TABLE usuarios ADD CONSTRAINT chk_usuarios_rol CHECK (rol IN ('administrador','residente'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
