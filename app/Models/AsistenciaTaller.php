<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsistenciaTaller extends Model
{
    use HasFactory;
    protected $table = 'asistencia_taller'; // Aquí especificas el nombre de la tabla
    protected $fillable = [
        'id',
        'user_id',
        'taller_id'
    ];
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function taller(){
        return $this->belongsTo(Taller::class,'taller_id');
    }
}
