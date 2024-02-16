<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mochila extends Model{
    use HasFactory;
    protected $table = 'mochila'; // Aquí especificas el nombre de la tabla
    protected $fillable = [
        'id',
        'user_id',
    ];
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
