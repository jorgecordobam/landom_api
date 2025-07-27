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
        Schema::create('documentos_instancias', function (Blueprint $table) {
            $table->id('id_documento_instancia');
            $table->unsignedBigInteger('id_plantilla');
            $table->unsignedBigInteger('id_proyecto');
            $table->string('nombre_instancia', 255);
            $table->string('url_documento_generado', 500)->nullable();
            $table->json('firmantes_info')->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_firma')->nullable();
            $table->enum('estado_firma', ['Borrador', 'Pendiente Firma', 'Firmado', 'Anulado'])->default('Borrador');
            
            $table->foreign('id_plantilla')->references('id_plantilla')->on('documentos_legal_plantillas')
                  ->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('id_proyecto')->references('id_proyecto')->on('proyectos')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_instancias');
    }
}; 