<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadosMexico extends Model
{
    use HasFactory;
    protected $table = 'estados_mexico'; // Aquí especificas el nombre de la tabla
    public $timestamps = false;
    protected $fillable = [
        'id',
        'estado',
    ]; 
}
