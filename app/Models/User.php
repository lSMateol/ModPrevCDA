<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Hidden(['password', 'remember_token', 'secret_answer'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'idper',
        'idemp',
        'username',
        'secret_answer', // Respuesta secreta para recuperación de contraseña (hasheada)
        'secret_question', // Clave de la pregunta secreta seleccionada
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
            'password' => 'hashed',
            // secret_answer NO se incluye aquí para evitar doble hashing.
            // El hash se aplica manualmente en los controladores, igual que password.
        ];
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idper', 'idper');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'idemp', 'idemp');
    }
}
