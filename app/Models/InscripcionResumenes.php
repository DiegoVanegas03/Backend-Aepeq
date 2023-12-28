<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InscripcionResumenes extends Model
{
    use HasFactory;

    protected $table = 'inscripcion_resumenes'; // Aquí especificas el nombre de la tabla

    protected $fillable = [
        'user_id',
        'nombre_archivo',
        'nombre_investigador',
        'apellidos_investigador',
        'estado_revision'
    ]; 

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
