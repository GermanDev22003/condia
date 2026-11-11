<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->increments('id_unidad');
            $table->unsignedInteger('id_condominio');
            $table->string('numero', 20);
            $table->string('torre_bloque', 50)->nullable();
            $table->string('nivel', 20)->nullable();

            $table->foreign('id_condominio')->references('id_condominio')->on('condominios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades');
    }
};
