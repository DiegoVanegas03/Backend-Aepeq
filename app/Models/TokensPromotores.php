<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokensPromotores extends Model
{
    use HasFactory;
    protected $table = 'tokens_promotores'; // Aquí especificas el nombre de la tabla

    protected $fillable = [
        'token',
        'fecha_expiracion'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_expiracion' => 'datetime',
    ];
}
