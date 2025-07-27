<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MensajeChat extends Model
{
    use HasFactory;

    protected $table = 'mensajes_chat';
    protected $primaryKey = 'id_mensaje';

    protected $fillable = [
        'id_proyecto',
        'id_emisor',
        'contenido',
        'id_mensaje_padre',
        'leido_por',
    ];

    protected $casts = [
        'fecha_envio' => 'datetime',
        'leido_por' => 'array',
    ];

    /**
     * Get the project that owns this chat message.
     */
    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'id_proyecto');
    }

    /**
     * Get the user who sent this message.
     */
    public function emisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_emisor');
    }

    /**
     * Get the parent message (for threaded conversations).
     */
    public function mensajePadre(): BelongsTo
    {
        return $this->belongsTo(MensajeChat::class, 'id_mensaje_padre');
    }
} 