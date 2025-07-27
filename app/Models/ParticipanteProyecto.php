<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipanteProyecto extends Model
{
    use HasFactory;

    protected $table = 'participantes_proyecto';
    protected $primaryKey = 'id_participante_proyecto';

    protected $fillable = [
        'id_proyecto',
        'id_usuario',
        'rol_en_proyecto',
        'estado_participacion',
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
    ];

    /**
     * Get the project that owns this participant.
     */
    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'id_proyecto');
    }

    /**
     * Get the user who is participating in the project.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
} 