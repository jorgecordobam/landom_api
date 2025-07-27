<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Propiedad extends Model
{
    use HasFactory;

    protected $table = 'propiedades';
    protected $primaryKey = 'id_propiedad';

    protected $fillable = [
        'direccion',
        'ciudad',
        'estado',
        'codigo_postal',
        'descripcion_corta',
        'metros_cuadrados_construccion',
        'metros_cuadrados_terreno',
        'numero_habitaciones',
        'numero_banos',
        'urls_fotos_actuales',
        'urls_planos',
        'estado_actual_descripcion',
        'id_propietario_registrador',
    ];

    protected $casts = [
        'urls_fotos_actuales' => 'array',
        'urls_planos' => 'array',
        'metros_cuadrados_construccion' => 'decimal:2',
        'metros_cuadrados_terreno' => 'decimal:2',
        'numero_banos' => 'decimal:1',
        'fecha_registro' => 'datetime',
    ];

    /**
     * Get the user who registered this property.
     */
    public function propietarioRegistrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_propietario_registrador');
    }

    /**
     * Get the project associated with this property.
     */
    public function proyecto(): HasOne
    {
        return $this->hasOne(Project::class, 'id_propiedad');
    }
} 