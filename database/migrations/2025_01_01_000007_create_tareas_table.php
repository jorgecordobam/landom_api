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
        Schema::create('tareas', function (Blueprint $table) {
            $table->id('id_tarea');
            $table->unsignedBigInteger('id_fase');
            $table->string('nombre_tarea', 255);
            $table->text('descripcion')->nullable();
            $table->date('fecha_vencimiento_estimada')->nullable();
            $table->unsignedBigInteger('id_responsable')->nullable();
            $table->enum('estado_tarea', ['Pendiente', 'En Curso', 'Completada', 'Bloqueada', 'Cancelada'])->default('Pendiente');
            $table->decimal('progreso_porcentaje', 5, 2)->default(0.00);
            
            $table->foreign('id_fase')->references('id_fase')->on('fases_proyectos')
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_responsable')->references('id_usuario')->on('usuarios')
                  ->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
}; 