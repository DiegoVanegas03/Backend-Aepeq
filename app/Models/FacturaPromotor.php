<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaPromotor extends Model
{
    use HasFactory;
    protected $table = 'facturas_promotores';

    protected $fillable = ['promotor_id', 'cfdi', 'factura_realizada', 'factura'];

    // Relación con el modelo de Usuario
    public function promotor()
    {
        return $this->belongsTo(Promotor::class, 'promotor_id');
    }
}
