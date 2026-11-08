<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condominios', function (Blueprint $table) {
            $table->increments('id_condominio');
            $table->string('nombre', 100)->unique();
            $table->string('tipo', 50);
            $table->string('direccion', 255)->nullable();
            $table->string('telefono', 20)->nullable();
        });

        DB::statement("ALTER TABLE condominios ADD CONSTRAINT chk_condominios_tipo CHECK (tipo IN ('edificio','residencial'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('condominios');
    }
};
