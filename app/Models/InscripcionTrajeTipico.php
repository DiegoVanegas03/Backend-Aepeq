<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InscripcionTrajeTipico extends Model
{
    use HasFactory;

    protected $table = 'inscripcion_traje';

    protected $fillable = [
        'primer_participante',
        'segundo_participante',
        'estado_representante',
        'nombre_doc',
        'nombre_pista',
    ];

    // Relación con el primer participante
    public function primerParticipante()
    {
        return $this->belongsTo(User::class, 'primer_participante');
    }

    // Relación con el segundo participante
    public function segundoParticipante()
    {
        return $this->belongsTo(User::class, 'segundo_participante');
    }

    public function estado()
    {
        return $this->belongsTo(EstadosMexico::class, 'estado_representante');
    }
}

