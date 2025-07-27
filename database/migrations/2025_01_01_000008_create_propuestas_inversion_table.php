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
        Schema::create('propuestas_inversion', function (Blueprint $table) {
            $table->id('id_propuesta');
            $table->unsignedBigInteger('id_proyecto')->unique();
            $table->string('titulo_propuesta', 255);
            $table->text('descripcion_financiera')->nullable();
            $table->decimal('monto_financiacion_requerido', 15, 2);
            $table->decimal('retorno_inversion_proyectado_porcentaje', 5, 2);
            $table->integer('plazo_inversion_meses')->nullable();
            $table->string('url_documento_completo', 500)->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->enum('estado_propuesta', ['Borrador', 'Activa', 'Financiada', 'Cerrada', 'Rechazada'])->default('Borrador');
            
            $table->foreign('id_proyecto')->references('id_proyecto')->on('proyectos')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('propuestas_inversion');
    }
}; 