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
        Schema::create('mensajes_chat', function (Blueprint $table) {
            $table->id('id_mensaje');
            $table->unsignedBigInteger('id_proyecto');
            $table->unsignedBigInteger('id_emisor');
            $table->text('contenido');
            $table->timestamp('fecha_envio')->useCurrent();
            $table->unsignedBigInteger('id_mensaje_padre')->nullable();
            $table->json('leido_por')->nullable();
            
            $table->foreign('id_proyecto')->references('id_proyecto')->on('proyectos')
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_emisor')->references('id_usuario')->on('usuarios')
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_mensaje_padre')->references('id_mensaje')->on('mensajes_chat')
                  ->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mensajes_chat');
    }
}; 