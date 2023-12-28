<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;


class Promotor extends Model 
{
    use HasFactory, Notifiable;

    protected $table = 'promotores'; // Aquí especificas el nombre de la tabla

    protected $fillable = [
        'email',
        'nombre',
        'numero_de_becas',
        'precio_por_beca',
        'precio_total',
        'comprobante_de_pago',
    ];
}
