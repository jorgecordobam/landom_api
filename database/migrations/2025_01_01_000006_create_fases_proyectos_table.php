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
        Schema::create('fases_proyectos', function (Blueprint $table) {
            $table->id('id_fase');
            $table->unsignedBigInteger('id_proyecto');
            $table->string('nombre_fase', 255);
            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio_estimada')->nullable();
            $table->date('fecha_fin_estimada')->nullable();
            $table->date('fecha_inicio_real')->nullable();
            $table->date('fecha_fin_real')->nullable();
            $table->enum('estado_fase', ['Por Iniciar', 'En Progreso', 'Completada', 'Retrasada'])->default('Por Iniciar');
            $table->integer('orden');
            
            $table->foreign('id_proyecto')->references('id_proyecto')->on('proyectos')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fases_proyectos');
    }
}; 