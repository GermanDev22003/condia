<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->increments('id_pago');
            $table->unsignedInteger('id_cuota');
            $table->unsignedInteger('id_usuario_registro');
            $table->decimal('monto_pagado', 10, 2);
            $table->date('fecha_pago');
            $table->string('metodo_pago', 30);
            $table->string('referencia', 50)->nullable();

            $table->foreign('id_cuota')->references('id_cuota')->on('cuotas');
            $table->foreign('id_usuario_registro')->references('id_usuario')->on('usuarios');
        });

        DB::statement('ALTER TABLE pagos ADD CONSTRAINT chk_pagos_monto_pagado CHECK (monto_pagado > 0)');
        DB::statement("ALTER TABLE pagos ADD CONSTRAINT chk_pagos_metodo_pago CHECK (metodo_pago IN ('efectivo','transferencia','cheque'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
