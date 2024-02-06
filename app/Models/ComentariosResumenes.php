<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComentariosResumenes extends Model
{
    use HasFactory;
    protected $table = 'comentarios_resumenes'; // Aquí especificas el nombre de la tabla

    protected $fillable = [
        'comentario',
        'inscripcion_id'
    ];
    public function inscripcion()
    {
        return $this->belongsTo(InscripcionResumenes::class,'inscripcion_id');
    }
}
