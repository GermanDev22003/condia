<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuotas', function (Blueprint $table) {
            $table->increments('id_cuota');
            $table->unsignedInteger('id_residente');
            $table->string('concepto', 100);
            $table->decimal('monto', 10, 2);
            $table->char('periodo', 7);
            $table->date('fecha_vencimiento');
            $table->string('estado', 30)->default('pendiente');

            $table->foreign('id_residente')->references('id_residente')->on('residentes');
        });

        DB::statement('ALTER TABLE cuotas ADD CONSTRAINT chk_cuotas_monto CHECK (monto > 0)');
        DB::statement("ALTER TABLE cuotas ADD CONSTRAINT chk_cuotas_estado CHECK (estado IN ('pendiente','pagada','vencida'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('cuotas');
    }
};
