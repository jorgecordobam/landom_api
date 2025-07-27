<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerfilConstructorContratista extends Model
{
    use HasFactory;

    protected $table = 'perfiles_constructores_contratistas';
    protected $primaryKey = 'id_perfil_constructor_contratista';

    protected $fillable = [
        'id_usuario',
        'nombre_empresa',
        'nit_o_registro_empresa',
        'url_certificado_registro_empresa',
        'url_licencia_contratista',
        'url_seguro_responsabilidad',
        'url_seguro_compensacion',
        'url_portafolio_proyectos',
        'contacto_legal_nombre',
        'contacto_legal_email',
        'contacto_legal_telefono',
        'url_contrato_marco_landonpro',
    ];

    protected $casts = [
        'url_portafolio_proyectos' => 'array',
    ];

    /**
     * Get the user that owns this contractor profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
} 