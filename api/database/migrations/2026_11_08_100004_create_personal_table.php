<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal', function (Blueprint $table) {
            $table->increments('id_personal');
            $table->unsignedInteger('id_condominio');
            $table->unsignedInteger('id_usuario')->nullable()->unique();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('cargo', 50);
            $table->string('turno', 30);
            $table->string('telefono', 20)->nullable();
            $table->string('estado', 30)->default('activo');

            $table->foreign('id_condominio')->references('id_condominio')->on('condominios');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
        });

        DB::statement("ALTER TABLE personal ADD CONSTRAINT chk_personal_cargo CHECK (cargo IN ('mantenimiento','seguridad'))");
        DB::statement("ALTER TABLE personal ADD CONSTRAINT chk_personal_turno CHECK (turno IN ('diurno','nocturno','mixto'))");
        DB::statement("ALTER TABLE personal ADD CONSTRAINT chk_personal_estado CHECK (estado IN ('activo','inactivo'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('personal');
    }
};
