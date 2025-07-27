<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentoLegalPlantilla extends Model
{
    use HasFactory;

    protected $table = 'documentos_legal_plantillas';
    protected $primaryKey = 'id_plantilla';

    protected $fillable = [
        'nombre_plantilla',
        'tipo_documento',
        'url_plantilla',
        'id_creador',
    ];

    protected $casts = [
        'fecha_subida' => 'datetime',
    ];

    /**
     * Get the user who created this template.
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_creador');
    }

    /**
     * Get the document instances created from this template.
     */
    public function instancias(): HasMany
    {
        return $this->hasMany(DocumentoInstancia::class, 'id_plantilla');
    }
} 