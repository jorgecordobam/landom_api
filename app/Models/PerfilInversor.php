<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerfilInversor extends Model
{
    use HasFactory;

    protected $table = 'perfiles_inversores';
    protected $primaryKey = 'id_perfil_inversor';

    protected $fillable = [
        'id_usuario',
        'url_id_oficial',
        'url_prueba_fondos',
        'url_formulario_tributario',
        'url_contrato_inversion_marco',
        'url_perfil_riesgo',
        'url_verificacion_direccion',
        'es_acreditado',
        'url_antecedentes_financieros_legales',
    ];

    protected $casts = [
        'es_acreditado' => 'boolean',
    ];

    /**
     * Get the user that owns this investor profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /**
     * Get the investments made by this investor.
     */
    public function inversiones(): HasMany
    {
        return $this->hasMany(Inversion::class, 'id_inversor');
    }
} 