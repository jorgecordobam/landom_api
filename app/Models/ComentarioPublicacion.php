<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComentarioPublicacion extends Model
{
    use HasFactory;

    protected $table = 'comentarios_publicaciones';
    protected $primaryKey = 'id_comentario';

    protected $fillable = [
        'id_publicacion',
        'id_autor',
        'contenido',
        'id_comentario_padre',
    ];

    protected $casts = [
        'fecha_comentario' => 'datetime',
    ];

    /**
     * Get the publication that owns this comment.
     */
    public function publicacion(): BelongsTo
    {
        return $this->belongsTo(Publicacion::class, 'id_publicacion');
    }

    /**
     * Get the user who made this comment.
     */
    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_autor');
    }

    /**
     * Get the parent comment (for nested comments).
     */
    public function comentarioPadre(): BelongsTo
    {
        return $this->belongsTo(ComentarioPublicacion::class, 'id_comentario_padre');
    }
} 