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
        Schema::create('comentarios_publicaciones', function (Blueprint $table) {
            $table->id('id_comentario');
            $table->unsignedBigInteger('id_publicacion');
            $table->unsignedBigInteger('id_autor');
            $table->text('contenido');
            $table->timestamp('fecha_comentario')->useCurrent();
            $table->unsignedBigInteger('id_comentario_padre')->nullable();
            
            $table->foreign('id_publicacion')->references('id_publicacion')->on('publicaciones')
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_autor')->references('id_usuario')->on('usuarios')
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_comentario_padre')->references('id_comentario')->on('comentarios_publicaciones')
                  ->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comentarios_publicaciones');
    }
}; 