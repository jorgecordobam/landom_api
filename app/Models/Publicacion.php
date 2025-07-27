<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publicacion extends Model
{
    use HasFactory;

    protected $table = 'publicaciones';
    protected $primaryKey = 'id_publicacion';

    protected $fillable = [
        'id_autor',
        'titulo',
        'contenido_html',
        'estado_publicacion',
        'url_imagen_principal',
    ];

    protected $casts = [
        'fecha_publicacion' => 'datetime',
    ];

    /**
     * Get the user who created this publication.
     */
    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_autor');
    }

    /**
     * Get the comments for this publication.
     */
    public function comentarios(): HasMany
    {
        return $this->hasMany(ComentarioPublicacion::class, 'id_publicacion');
    }
} 