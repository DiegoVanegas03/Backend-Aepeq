<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CambioDeAula extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $aula;
    public $nombre;
    public function __construct($aula, $nombre)
    {
        $this->aula = $aula;
        $this->nombre = $nombre;
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
        $url = 'https://aepeq.mx/talleres';
        return (new MailMessage)
                    ->subject('Cambio de Aula')
                    ->greeting('Hola  '.$notifiable->nombres)
                    ->line('Somos el equipo de AEPEQ, nos comunicamos para notificarte que el taller que tenias inscrito "'.$this->nombre.'" ha cambiado a la aula "' . $this->aula . '".')
                    ->line('Para mas información comunicate con nosotros a traves de nuestro whatsapp: +52 444 438 9723.')
                    ->action('IR A TALLERES', $url)
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
