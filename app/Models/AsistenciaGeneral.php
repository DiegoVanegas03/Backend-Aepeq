<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsistenciaGeneral extends Model
{
    use HasFactory;
    protected $table = 'asistencia_general'; // Aquí especificas el nombre de la tabla

    protected $fillable = [
        'id',
        'user_id',
        'dia'
    ];
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
