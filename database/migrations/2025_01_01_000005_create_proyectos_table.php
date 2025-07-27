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
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id('id_proyecto');
            $table->unsignedBigInteger('id_propiedad')->unique();
            $table->string('nombre_proyecto', 255);
            $table->unsignedBigInteger('id_gerente_proyecto');
            $table->text('descripcion_detallada')->nullable();
            $table->decimal('presupuesto_estimado_total', 15, 2)->nullable();
            $table->decimal('roi_estimado_porcentaje', 5, 2)->nullable();
            $table->date('fecha_inicio_estimada')->nullable();
            $table->date('fecha_fin_estimada')->nullable();
            $table->date('fecha_inicio_real')->nullable();
            $table->date('fecha_fin_real')->nullable();
            $table->enum('estado_proyecto', [
                'Borrador', 'Propuesta Abierta', 'Financiado', 'En Ejecución', 
                'En Pausa', 'Completado', 'Cancelado'
            ])->default('Borrador');
            
            $table->foreign('id_propiedad')->references('id_propiedad')->on('propiedades')
                  ->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('id_gerente_proyecto')->references('id_usuario')->on('usuarios')
                  ->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
}; 