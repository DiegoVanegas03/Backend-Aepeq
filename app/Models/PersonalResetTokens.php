<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalResetTokens extends Model
{
    use HasFactory;
    protected $table = 'password_reset_tokens'; // Aquí especificas el nombre de la tabla

    protected $fillable = [
        'id',
        'email',
        'token',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];
}

