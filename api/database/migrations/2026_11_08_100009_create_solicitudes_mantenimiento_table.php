<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_mantenimiento', function (Blueprint $table) {
            $table->increments('id_solicitud');
            $table->unsignedInteger('id_residente');
            $table->unsignedInteger('id_personal')->nullable();
            $table->string('titulo', 150);
            $table->string('ubicacion', 150);
            $table->string('prioridad', 20)->default('media');
            $table->string('estado', 20)->default('pendiente');
            $table->text('descripcion');
            $table->dateTime('fecha_registro')->useCurrent();

            $table->foreign('id_residente')->references('id_residente')->on('residentes');
            $table->foreign('id_personal')->references('id_personal')->on('personal');
        });

        DB::statement("ALTER TABLE solicitudes_mantenimiento ADD CONSTRAINT chk_solicitudes_prioridad CHECK (prioridad IN ('baja','media','alta'))");
        DB::statement("ALTER TABLE solicitudes_mantenimiento ADD CONSTRAINT chk_solicitudes_estado CHECK (estado IN ('pendiente','en_proceso','completada'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_mantenimiento');
    }
};
