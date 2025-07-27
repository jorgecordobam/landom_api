<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    use HasFactory;

    protected $table = 'proyectos';
    protected $primaryKey = 'id_proyecto';

    protected $fillable = [
        'id_propiedad',
        'nombre_proyecto',
        'id_gerente_proyecto',
        'descripcion_detallada',
        'presupuesto_estimado_total',
        'roi_estimado_porcentaje',
        'fecha_inicio_estimada',
        'fecha_fin_estimada',
        'fecha_inicio_real',
        'fecha_fin_real',
        'estado_proyecto',
    ];

    protected $casts = [
        'fecha_inicio_estimada' => 'date',
        'fecha_fin_estimada' => 'date',
        'fecha_inicio_real' => 'date',
        'fecha_fin_real' => 'date',
        'presupuesto_estimado_total' => 'decimal:2',
        'roi_estimado_porcentaje' => 'decimal:2',
    ];

    /**
     * Get the property that owns this project.
     */
    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class, 'id_propiedad');
    }

    /**
     * Get the project manager.
     */
    public function gerenteProyecto(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_gerente_proyecto');
    }

    /**
     * Get the phases for this project.
     */
    public function fases(): HasMany
    {
        return $this->hasMany(FaseProyecto::class, 'id_proyecto');
    }

    /**
     * Get the investment proposal for this project.
     */
    public function propuestaInversion(): HasOne
    {
        return $this->hasOne(PropuestaInversion::class, 'id_proyecto');
    }

    /**
     * Get the tasks for this project (through phases).
     */
    public function tareas(): HasMany
    {
        return $this->hasManyThrough(Task::class, FaseProyecto::class, 'id_proyecto', 'id_fase');
    }

    /**
     * Get the chat messages for this project.
     */
    public function mensajesChat(): HasMany
    {
        return $this->hasMany(MensajeChat::class, 'id_proyecto');
    }

    /**
     * Get the document instances for this project.
     */
    public function documentosInstancias(): HasMany
    {
        return $this->hasMany(DocumentoInstancia::class, 'id_proyecto');
    }

    /**
     * Get the participants in this project.
     */
    public function participantes(): HasMany
    {
        return $this->hasMany(ParticipanteProyecto::class, 'id_proyecto');
    }

    /**
     * Get the users participating in this project.
     */
    public function usuariosParticipantes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'participantes_proyecto', 'id_proyecto', 'id_usuario')
                    ->withPivot('rol_en_proyecto', 'estado_participacion', 'fecha_asignacion');
    }
}
