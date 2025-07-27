<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_usuario';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'contrasena_hash',
        'telefono',
        'tipo_perfil',
        'estado_verificacion',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'contrasena_hash',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'fecha_registro' => 'datetime',
        ];
    }

    /**
     * Get the password attribute name for authentication.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->contrasena_hash;
    }

    /**
     * Check if user is administrator
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->tipo_perfil === 'Administrador';
    }

    /**
     * Get the investor profile for this user.
     */
    public function perfilInversor(): HasOne
    {
        return $this->hasOne(PerfilInversor::class, 'id_usuario');
    }

    /**
     * Get the worker profile for this user.
     */
    public function perfilTrabajador(): HasOne
    {
        return $this->hasOne(PerfilTrabajador::class, 'id_usuario');
    }

    /**
     * Get the contractor profile for this user.
     */
    public function perfilConstructorContratista(): HasOne
    {
        return $this->hasOne(PerfilConstructorContratista::class, 'id_usuario');
    }

    /**
     * Get the properties registered by this user.
     */
    public function propiedadesRegistradas(): HasMany
    {
        return $this->hasMany(Propiedad::class, 'id_propietario_registrador');
    }

    /**
     * Get the projects managed by this user.
     */
    public function proyectosGestionados(): HasMany
    {
        return $this->hasMany(Project::class, 'id_gerente_proyecto');
    }

    /**
     * Get the tasks assigned to this user.
     */
    public function tareasAsignadas(): HasMany
    {
        return $this->hasMany(Task::class, 'id_responsable');
    }

    /**
     * Get the chat messages sent by this user.
     */
    public function mensajesChat(): HasMany
    {
        return $this->hasMany(MensajeChat::class, 'id_emisor');
    }

    /**
     * Get the publications created by this user.
     */
    public function publicaciones(): HasMany
    {
        return $this->hasMany(Publicacion::class, 'id_autor');
    }

    /**
     * Get the comments made by this user.
     */
    public function comentarios(): HasMany
    {
        return $this->hasMany(ComentarioPublicacion::class, 'id_autor');
    }

    /**
     * Get the alerts for this user.
     */
    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class, 'id_usuario');
    }

    /**
     * Get the document templates created by this user.
     */
    public function plantillasDocumentos(): HasMany
    {
        return $this->hasMany(DocumentoLegalPlantilla::class, 'id_creador');
    }

    /**
     * Get the projects this user participates in.
     */
    public function participacionesProyecto(): HasMany
    {
        return $this->hasMany(ParticipanteProyecto::class, 'id_usuario');
    }

    /**
     * Get the investments made by this user (through investor profile).
     */
    public function inversiones(): HasMany
    {
        return $this->hasManyThrough(Inversion::class, PerfilInversor::class, 'id_usuario', 'id_inversor');
    }
}
