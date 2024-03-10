<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Notifications\RecuerdoQr;
use Barryvdh\DomPDF\Facade\Pdf;

class CorreoMassivo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    protected $registro;

    public function __construct($registro){
        $this->registro = $registro;
    }

    /**
     * Execute the job.
     */
    public function handle(): void{
        $congresista = [
            'qr_image' => 'data:image/png;base64,'.$this->registro->qr_code,
            'nombre'=>$this->registro->nombres,
            'numero'=>$this->registro->id,
            'apellido'=>$this->registro->apellidos,
        ];
        $pdf = PDF::loadView('congresista.recordatorio_qr', compact('congresista'));
        $pdf->setPaper('A4'); 
        $pdf->setOption('chroot',realpath(''));
        $this->registro->notify(new RecuerdoQr($pdf));
    }
}
