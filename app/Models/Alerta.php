<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alerta extends Model
{
    use HasFactory;

    protected $table = 'alertas';
    protected $primaryKey = 'id_alerta';

    protected $fillable = [
        'id_usuario',
        'tipo_alerta',
        'mensaje',
        'id_entidad_referencia',
        'tipo_entidad_referencia',
        'leida',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'leida' => 'boolean',
    ];

    /**
     * Get the user who owns this alert.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
} 