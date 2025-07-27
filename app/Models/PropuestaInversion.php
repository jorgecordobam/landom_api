<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropuestaInversion extends Model
{
    use HasFactory;

    protected $table = 'propuestas_inversion';
    protected $primaryKey = 'id_propuesta';

    protected $fillable = [
        'id_proyecto',
        'titulo_propuesta',
        'descripcion_financiera',
        'monto_financiacion_requerido',
        'retorno_inversion_proyectado_porcentaje',
        'plazo_inversion_meses',
        'url_documento_completo',
        'estado_propuesta',
    ];

    protected $casts = [
        'monto_financiacion_requerido' => 'decimal:2',
        'retorno_inversion_proyectado_porcentaje' => 'decimal:2',
        'fecha_creacion' => 'datetime',
    ];

    /**
     * Get the project that owns this investment proposal.
     */
    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'id_proyecto');
    }

    /**
     * Get the investments for this proposal.
     */
    public function inversiones(): HasMany
    {
        return $this->hasMany(Inversion::class, 'id_propuesta');
    }
} 