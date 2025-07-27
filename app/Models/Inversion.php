<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inversion extends Model
{
    use HasFactory;

    protected $table = 'inversiones';
    protected $primaryKey = 'id_inversion';

    protected $fillable = [
        'id_propuesta',
        'id_inversor',
        'monto_invertido',
        'participacion_porcentaje_proyecto',
        'estado_inversion',
    ];

    protected $casts = [
        'monto_invertido' => 'decimal:2',
        'participacion_porcentaje_proyecto' => 'decimal:2',
        'fecha_inversion' => 'datetime',
    ];

    /**
     * Get the investment proposal that owns this investment.
     */
    public function propuesta(): BelongsTo
    {
        return $this->belongsTo(PropuestaInversion::class, 'id_propuesta');
    }

    /**
     * Get the investor who made this investment.
     */
    public function inversor(): BelongsTo
    {
        return $this->belongsTo(PerfilInversor::class, 'id_inversor');
    }
} 