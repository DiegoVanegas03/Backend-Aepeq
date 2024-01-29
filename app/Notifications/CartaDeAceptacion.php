<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CartaDeAceptacion extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $pdf;
    public $nombre;
    public $id_inscripcion;
    public function __construct($pdf,$nombre, $id_inscripcion)
    {
        $this->pdf = $pdf;
        $this->nombre = $nombre;
        $this->id_inscripcion = $id_inscripcion;
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
        $metadata = '?id_inscripcion='.$this->id_inscripcion.'&id_usuario='.$notifiable->id;
        $url = 'https://aepeq.mx/actividades/documento_final?metadata='.base64_encode($metadata);  
        return (new MailMessage)
                    ->subject('Dictamen de aceptación de resumen')
                    ->greeting('Hola  '.$notifiable->nombres)
                    ->line('Somos el equipo de AEPEQ, nos comunicamos para notificarte que se ah aceptado su resumen en la actividad de Trabajos Academicos.')
                    ->line('Por lo que por medio de este correo encontraras el enlace para subir tu documento final (extensión) junto con la carta de aceptación adjunta al correo.')
                    ->line('Para mas informacion comunicate con nosotros a traves de nuestro whatsapp: +52 444 438 9723.')
                    ->line('Para subir el documento presiona el boton:')
                    ->action('PRESIONAME', $url)
                    ->line('Te esperamos, por favor no responder a este correo.')
                    ->attachData($this->pdf,  $this->nombre, [
                        'mime' => 'application/pdf',
                    ]);
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
