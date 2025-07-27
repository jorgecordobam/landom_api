<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoInstancia extends Model
{
    use HasFactory;

    protected $table = 'documentos_instancias';
    protected $primaryKey = 'id_documento_instancia';

    protected $fillable = [
        'id_plantilla',
        'id_proyecto',
        'nombre_instancia',
        'url_documento_generado',
        'firmantes_info',
        'fecha_firma',
        'estado_firma',
    ];

    protected $casts = [
        'firmantes_info' => 'array',
        'fecha_creacion' => 'datetime',
        'fecha_firma' => 'datetime',
    ];

    /**
     * Get the template that was used to create this document instance.
     */
    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(DocumentoLegalPlantilla::class, 'id_plantilla');
    }

    /**
     * Get the project that owns this document instance.
     */
    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'id_proyecto');
    }
} 