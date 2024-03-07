<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConstanciaGeneral extends Model
{
    use HasFactory;
    protected $table = 'constancias_general'; // Aquí especificas el nombre de la tabla
    protected $fillable = [
        'id',
        'user_id',
        'nombre_doc',
        'correccion',
    ];
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
