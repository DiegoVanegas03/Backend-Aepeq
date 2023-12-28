<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InscripcionTaller extends Model
{
    use HasFactory;
    
    protected $table = 'inscripcion_talleres';

    protected $fillable = ['user_id','taller_id'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function taller()
    {
        return $this->belongsTo(Taller::class,'taller_id');
    }
}
?>