<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokensRegistro extends Model
{
    use HasFactory;
    protected $table = 'token_de_registro'; // Aquí especificas el nombre de la tabla

    protected $fillable = [
        'token_de_registro',
        'nombre',
        'estado_del_registro',
        'tipo_inscripcion',
        'promotor_id'
    ];

    public function promotor()
    {
        return $this->belongsTo(Promotor::class, 'promotor_id');
    }
}
