<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FaseProyecto extends Model
{
    use HasFactory;

    protected $table = 'fases_proyectos';
    protected $primaryKey = 'id_fase';

    protected $fillable = [
        'id_proyecto',
        'nombre_fase',
        'descripcion',
        'fecha_inicio_estimada',
        'fecha_fin_estimada',
        'fecha_inicio_real',
        'fecha_fin_real',
        'estado_fase',
        'orden',
    ];

    protected $casts = [
        'fecha_inicio_estimada' => 'date',
        'fecha_fin_estimada' => 'date',
        'fecha_inicio_real' => 'date',
        'fecha_fin_real' => 'date',
    ];

    /**
     * Get the project that owns this phase.
     */
    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'id_proyecto');
    }

    /**
     * Get the tasks for this phase.
     */
    public function tareas(): HasMany
    {
        return $this->hasMany(Task::class, 'id_fase');
    }
} 