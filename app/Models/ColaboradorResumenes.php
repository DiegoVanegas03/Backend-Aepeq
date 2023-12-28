<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ColaboradorResumenes extends Model
{
    use HasFactory;
    protected $table = 'colaboradores_resumenes'; // Aquí especificas el nombre de la tabla

    protected $fillable = [
        'nombres',
        'apellidos',
        'inscripcion_id'
    ];

    public function inscripcion()
    {
        return $this->belongsTo(InscripcionResumenes::class,'inscripcion_id');
    }
}
