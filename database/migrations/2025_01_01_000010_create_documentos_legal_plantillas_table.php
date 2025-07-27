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
        Schema::create('documentos_legal_plantillas', function (Blueprint $table) {
            $table->id('id_plantilla');
            $table->string('nombre_plantilla', 255);
            $table->enum('tipo_documento', ['Contrato Inversion', 'Acuerdo Obra', 'Contrato Servicio', 'Politica Interna', 'Otro']);
            $table->string('url_plantilla', 500);
            $table->timestamp('fecha_subida')->useCurrent();
            $table->unsignedBigInteger('id_creador')->nullable();
            
            $table->foreign('id_creador')->references('id_usuario')->on('usuarios')
                  ->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_legal_plantillas');
    }
}; 