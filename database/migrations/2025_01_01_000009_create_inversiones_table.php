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
        Schema::create('inversiones', function (Blueprint $table) {
            $table->id('id_inversion');
            $table->unsignedBigInteger('id_propuesta');
            $table->unsignedBigInteger('id_inversor');
            $table->decimal('monto_invertido', 15, 2);
            $table->timestamp('fecha_inversion')->useCurrent();
            $table->decimal('participacion_porcentaje_proyecto', 5, 2)->nullable();
            $table->enum('estado_inversion', ['Pendiente', 'Confirmada', 'Reembolsada'])->default('Pendiente');
            
            $table->foreign('id_propuesta')->references('id_propuesta')->on('propuestas_inversion')
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_inversor')->references('id_perfil_inversor')->on('perfiles_inversores')
                  ->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inversiones');
    }
}; 