<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('propiedades', function (Blueprint $table) {
            $table->id('id_propiedad');
            $table->string('direccion', 255);
            $table->string('ciudad', 100);
            $table->string('estado', 100)->nullable();
            $table->string('codigo_postal', 20)->nullable();
            $table->text('descripcion_corta')->nullable();
            $table->decimal('metros_cuadrados_construccion', 10, 2)->nullable();
            $table->decimal('metros_cuadrados_terreno', 10, 2)->nullable();
            $table->integer('numero_habitaciones')->nullable();
            $table->decimal('numero_banos', 3, 1)->nullable();
            $table->json('urls_fotos_actuales')->nullable();
            $table->json('urls_planos')->nullable();
            $table->text('estado_actual_descripcion')->nullable();
            $table->timestamp('fecha_registro')->useCurrent();
            $table->unsignedBigInteger('id_propietario_registrador');
            
            $table->foreign('id_propietario_registrador')->references('id_usuario')->on('usuarios')
                  ->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('propiedades');
    }
}; 