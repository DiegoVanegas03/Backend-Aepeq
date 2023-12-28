<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taller extends Model
{
    use HasFactory;
    protected $table = 'talleres'; // Aquí especificas el nombre de la tabla

    protected $fillable = [
        'id',
        'aula',
        'nombre_taller',
        'ponente',
        'descripcion',
        'capacidad_maxima',
        'dia',
    ];
}
