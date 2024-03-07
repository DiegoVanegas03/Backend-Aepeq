<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluacion extends Model
{
    use HasFactory;
    protected $table = 'evaluacion'; // Aquí especificas el nombre de la tabla
    protected $fillable = [
        'id',
        'user_id',
        'repuestas_select',
        'mas_gustado',
        'menos_gustado',
        'mejoras'
    ];
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
