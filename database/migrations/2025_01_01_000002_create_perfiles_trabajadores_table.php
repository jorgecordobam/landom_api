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
        Schema::create('perfiles_trabajadores', function (Blueprint $table) {
            $table->id('id_perfil_trabajador');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('url_id_oficial', 500)->nullable();
            $table->string('numero_seguro_social_itin_hash', 255)->nullable();
            $table->json('url_certificados_capacitacion')->nullable();
            $table->string('url_curriculum', 500)->nullable();
            $table->text('experiencia_laboral')->nullable();
            $table->string('url_foto_carnet', 500)->nullable();
            $table->enum('disponibilidad_actual', ['Disponible', 'Ocupado', 'De Vacaciones', 'No Asignado'])->default('Disponible');
            
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perfiles_trabajadores');
    }
}; 