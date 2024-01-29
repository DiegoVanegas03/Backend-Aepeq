<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CorreccionesResumen extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $correcciones;
    public $id_inscripcion;
    public function __construct($correciones, $id_inscripcion)
    {
        $this->correcciones = $correciones;
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
        $metadata = '?id_inscripcion='.$this->id_inscripcion.'&id_usuario='.$notifiable->id.'&segunda_opcion=true';
        $url = 'https://aepeq.mx/actividades/documento_final?metadata='.base64_encode($metadata);  
        $message = new MailMessage();
        $message->subject('Correciones en extension')
                ->greeting('Hola  '.$notifiable->nombres)
                ->line('Somos el equipo de AEPEQ, nos comunicamos para notificarte que se solicitan correciones en tu extension subida en Trabajos Academicos.')
                ->line('Por lo que por medio de este correo encontraras el enlace para subir tu documento final (extensión).')
                ->line('Para mas informacion comunicate con nosotros a traves de nuestro whatsapp: +52 444 438 9723.')
                ->line('Las correcciones son las siguientes:');

        foreach($this->correcciones as $numero=>$correccion){
            $message->line(($numero+1).'.-'.$correccion);
        }
        $message->line('Para subir el documento presiona el boton:')
                ->action('PRESIONAME', $url)
                ->line('Te esperamos, por favor no responder a este correo.');
        return ($message);
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
