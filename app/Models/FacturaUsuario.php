<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaUsuario extends Model
{
    use HasFactory;

    protected $table = 'facturas_usuario';

    protected $fillable = ['user_id', 'cfdi', 'factura_realizada', 'factura'];

    // Relación con el modelo de Usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Otros métodos o propiedades del modelo, según sea necesario...

}
