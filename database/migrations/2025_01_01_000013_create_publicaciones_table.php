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
        Schema::create('publicaciones', function (Blueprint $table) {
            $table->id('id_publicacion');
            $table->unsignedBigInteger('id_autor');
            $table->string('titulo', 255);
            $table->longText('contenido_html');
            $table->timestamp('fecha_publicacion')->useCurrent();
            $table->enum('estado_publicacion', ['Borrador', 'Publicado', 'Archivado'])->default('Borrador');
            $table->string('url_imagen_principal', 500)->nullable();
            
            $table->foreign('id_autor')->references('id_usuario')->on('usuarios')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publicaciones');
    }
}; 