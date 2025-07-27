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
        Schema::create('perfiles_constructores_contratistas', function (Blueprint $table) {
            $table->id('id_perfil_constructor_contratista');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('nombre_empresa', 255);
            $table->string('nit_o_registro_empresa', 100)->unique()->nullable();
            $table->string('url_certificado_registro_empresa', 500)->nullable();
            $table->string('url_licencia_contratista', 500)->nullable();
            $table->string('url_seguro_responsabilidad', 500)->nullable();
            $table->string('url_seguro_compensacion', 500)->nullable();
            $table->json('url_portafolio_proyectos')->nullable();
            $table->string('contacto_legal_nombre', 255)->nullable();
            $table->string('contacto_legal_email', 255)->nullable();
            $table->string('contacto_legal_telefono', 50)->nullable();
            $table->string('url_contrato_marco_landonpro', 500)->nullable();
            
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perfiles_constructores_contratistas');
    }
}; 