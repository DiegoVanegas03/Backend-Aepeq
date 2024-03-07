<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConstanciaTaller extends Model
{
    use HasFactory;
    protected $table = 'constancias_taller'; // Aquí especificas el nombre de la tabla
    protected $fillable = [
        'id',
        'ins_taller_id',
        'hoja',
        'folio',
        'nombre_doc',
        'correccion',
    ];
    public function inscripcion(){
        return $this->belongsTo(InscripcionTaller::class,'ins_taller_id');
    }
}
