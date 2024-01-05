<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PauseAccount extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
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
                    ->subject('Tu cuenata aepeq ha sido pausada')
                    ->greeting('Hola  '.$notifiable->nombres)
                    ->line('Somos el equipo de AEPEQ, nos comunicamos para notificarte que tu cuenta se encuentra pausada.')
                    ->line('Por lo que por el momento no podras iniciar sesion en la pagina principal, recuerda que para poder acceder
                    al congreso y a los talleres necesitaras tener tu cuenta activa.')
                    ->line('Para mas informacion comunicate con nosotros a traves de nuestro whatsapp: +52 444 438 9723.')
                    ->action('Pagina Principal', 'https://aepeq.mx')
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
