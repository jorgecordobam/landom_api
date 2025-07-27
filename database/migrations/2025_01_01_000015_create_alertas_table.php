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
        Schema::create('alertas', function (Blueprint $table) {
            $table->id('id_alerta');
            $table->unsignedBigInteger('id_usuario');
            $table->enum('tipo_alerta', [
                'Costo Excedido', 'Retraso Tarea', 'Nueva Inversion', 'Documento Pendiente', 
                'Mensaje Nuevo', 'Cambio Estado Proyecto', 'Otro'
            ]);
            $table->string('mensaje', 500);
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('id_entidad_referencia')->nullable();
            $table->enum('tipo_entidad_referencia', [
                'Proyecto', 'Tarea', 'PropuestaInversion', 'DocumentoInstancia', 
                'MensajeChat', 'Publicacion', 'Inversion'
            ])->nullable();
            $table->boolean('leida')->default(false);
            
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
}; 