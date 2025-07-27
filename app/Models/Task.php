<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $table = 'tareas';
    protected $primaryKey = 'id_tarea';

    protected $fillable = [
        'id_fase',
        'nombre_tarea',
        'descripcion',
        'fecha_vencimiento_estimada',
        'id_responsable',
        'estado_tarea',
        'progreso_porcentaje',
    ];

    protected $casts = [
        'fecha_vencimiento_estimada' => 'date',
        'progreso_porcentaje' => 'decimal:2',
    ];

    /**
     * Get the phase that owns this task.
     */
    public function fase(): BelongsTo
    {
        return $this->belongsTo(FaseProyecto::class, 'id_fase');
    }

    /**
     * Get the user assigned to this task.
     */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_responsable');
    }

    /**
     * Get the project that owns this task (through phase).
     */
    public function proyecto(): BelongsTo
    {
        return $this->fase->proyecto();
    }
}
