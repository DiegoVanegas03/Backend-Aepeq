<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DesinscripcionForzada extends Notification
{
    use Queueable;
    public $nombre_taller;

    /**
     * Create a new notification instance.
     */
    public function __construct($nombre_taller)
    {
        $this->nombre_taller = $nombre_taller;
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
        return (new MailMessage)
                    ->subject('Actualizacion de taller')
                    ->greeting('Hola  '.$notifiable->nombres)
                    ->line('Somos el equipo de AEPEQ, nos comunicamos para notificarte que se te tuvo que desinscribir al taller "'.$this->nombre_taller. '" debido a cambios inesperados.')
                    ->line('Te inivitamos a elegir de nuestra variedad de talleres restantes en el día.')
                    ->line('Para mas informacion comunicate con nosotros a traves de nuestro whatsapp: +52 444 438 9723.')
                    ->action('Ir a Talleres', 'https://aepeq.mx/talleres')
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
