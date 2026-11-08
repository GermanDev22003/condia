<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residentes', function (Blueprint $table) {
            $table->increments('id_residente');
            $table->unsignedInteger('id_usuario')->nullable()->unique();
            $table->unsignedInteger('id_unidad');
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('telefono', 20)->nullable();
            $table->date('fecha_ingreso');
            $table->string('estado', 30)->default('activo');

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
            $table->foreign('id_unidad')->references('id_unidad')->on('unidades');
        });

        DB::statement("ALTER TABLE residentes ADD CONSTRAINT chk_residentes_estado CHECK (estado IN ('activo','inactivo'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('residentes');
    }
};
