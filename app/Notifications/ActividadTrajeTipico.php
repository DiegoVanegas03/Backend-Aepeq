<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActividadTrajeTipico extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $inscripcion;
    public function __construct($inscripcion)
    {
        $this->inscripcion = $inscripcion;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $metadata ='id='.$this->inscripcion->id.'&primer_participante='.$this->inscripcion->primer_participante.'&segundo_participante='.$this->inscripcion->segundo_participante;
        $url = 'https://aepeq.mx/resolve_trajetipico?metadata='.base64_encode($metadata);  
        return (new MailMessage)
                    ->subject('Invitación actividad traje tipico')
                    ->greeting('Hola  '.$notifiable->nombres)
                    ->line('Somos el equipo de AEPEQ, nos comunicamos para notificarte que se te ah inivitado a formar parte de la actividad de traje tipico.')
                    ->line('Por lo que por medio de este correo podras aceptar o rechazar la oferta, si tu no reconoces esto favor de cancelar esta inscripción.')
                    ->line('Para mas informacion comunicate con nosotros a traves de nuestro whatsapp: +52 444 438 9723.')
                    ->line('Si deseas aceptar o cancelar la invitación presiona el siguiente boton:')
                    ->action('PRESIONAME', $url)
                    ->line('Te esperamos, por favor no responder a este correo.');
}

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
