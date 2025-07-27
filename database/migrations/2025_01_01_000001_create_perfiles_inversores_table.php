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
        Schema::create('perfiles_inversores', function (Blueprint $table) {
            $table->id('id_perfil_inversor');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('url_id_oficial', 500)->nullable();
            $table->string('url_prueba_fondos', 500)->nullable();
            $table->string('url_formulario_tributario', 500)->nullable();
            $table->string('url_contrato_inversion_marco', 500)->nullable();
            $table->string('url_perfil_riesgo', 500)->nullable();
            $table->string('url_verificacion_direccion', 500)->nullable();
            $table->boolean('es_acreditado')->default(false);
            $table->string('url_antecedentes_financieros_legales', 500)->nullable();
            
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perfiles_inversores');
    }
}; 