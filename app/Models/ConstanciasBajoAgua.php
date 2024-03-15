<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConstanciasBajoAgua extends Model
{
    use HasFactory;
    protected $table = 'constancias_bajo_agua'; // Aquí especificas el nombre de la tabla
    protected $fillable = [
        'id',
        'folio',
        'nombre_completo',
        'taller_id',
        'nombre_doc',
    ];
}
