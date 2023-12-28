<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'qr_code',
        'nombres',
        'apellidos',
        'email',
        'password',
        'numero_celular',
        'estado_provincia',
        'pais',
        'ocupacion',
        'lugar_trabajo',
        'tipo_inscripcion',
        'escuela',
        'asociacion',
        'documento_certificado',
        'beca_pago',
        'metodo_pago',
        'comprobante_pago',
        'promotor',
        'token_de_registro',
        'estado_del_registro',
        'rol',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function hasRole($role)
    {
        return $this->rol == $role;
    }

    public function pause(){
        $this->estado_del_registro = 0;
    }

    public function play(){
        $this->estado_del_registro = 2;
    }
}
