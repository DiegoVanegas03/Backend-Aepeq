<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    use HasFactory;

    protected $table = 'programa'; // Aquí especificas el nombre de la tabla

    protected $fillable = [
        'horario',
        'titulo_ponencia',
        'ponente',
        'subtitulo_ponencia',
        'dia',
        'tipo',
    ];    
}
