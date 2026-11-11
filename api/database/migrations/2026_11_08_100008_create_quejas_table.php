<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quejas', function (Blueprint $table) {
            $table->increments('id_queja');
            $table->unsignedInteger('id_residente');
            $table->string('asunto', 150);
            $table->string('categoria', 50);
            $table->text('descripcion');
            $table->string('estado', 30)->default('pendiente');
            $table->text('respuesta')->nullable();
            $table->dateTime('fecha_registro')->useCurrent();

            $table->foreign('id_residente')->references('id_residente')->on('residentes');
        });

        DB::statement("ALTER TABLE quejas ADD CONSTRAINT chk_quejas_categoria CHECK (categoria IN ('convivencia','limpieza','seguridad','ruido','infraestructura','otra'))");
        DB::statement("ALTER TABLE quejas ADD CONSTRAINT chk_quejas_estado CHECK (estado IN ('pendiente','en_proceso','resuelta'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('quejas');
    }
};
