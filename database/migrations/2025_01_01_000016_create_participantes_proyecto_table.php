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
        Schema::create('participantes_proyecto', function (Blueprint $table) {
            $table->id('id_participante_proyecto');
            $table->unsignedBigInteger('id_proyecto');
            $table->unsignedBigInteger('id_usuario');
            $table->enum('rol_en_proyecto', [
                'Gerente', 'Inversor', 'Constructor Principal', 'Subcontratista', 
                'Arquitecto', 'Trabajador', 'Auditor', 'Cliente Interesado', 'Otro'
            ]);
            $table->timestamp('fecha_asignacion')->useCurrent();
            $table->enum('estado_participacion', ['Activo', 'Inactivo', 'Completado'])->default('Activo');
            
            $table->unique(['id_proyecto', 'id_usuario', 'rol_en_proyecto']);
            
            $table->foreign('id_proyecto')->references('id_proyecto')->on('proyectos')
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participantes_proyecto');
    }
}; 