<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InscripcionFotografia extends Model
{
    use HasFactory;
    protected $table = 'inscripcion_fotografia'; // Aquí especificas el nombre de la tabla

    protected $fillable = [
        'user_id',
        'nombre_fotografia',
        'lugar_y_fecha',
        'descripcion',
        'documento',
    ]; 

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
