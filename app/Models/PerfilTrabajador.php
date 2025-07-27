<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerfilTrabajador extends Model
{
    use HasFactory;

    protected $table = 'perfiles_trabajadores';
    protected $primaryKey = 'id_perfil_trabajador';

    protected $fillable = [
        'id_usuario',
        'url_id_oficial',
        'numero_seguro_social_itin_hash',
        'url_certificados_capacitacion',
        'url_curriculum',
        'experiencia_laboral',
        'url_foto_carnet',
        'disponibilidad_actual',
    ];

    protected $casts = [
        'url_certificados_capacitacion' => 'array',
    ];

    /**
     * Get the user that owns this worker profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
} 