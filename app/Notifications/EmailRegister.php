<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailRegister extends Notification
{
    use Queueable;
    public $url;

    /**
     * Create a new notification instance.
     */
    public function __construct($url)
    {
        $this->url = $url;
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
                    ->greeting('Hola  '.$notifiable->nombres)
                    ->subject('Verificación de correo electrónico para AEPEQ')
                    ->line('Somos el equipo de AEPEQ, nos comunicamos para verificar tu correo electrónico.')
                    ->line('Por este medio, te estaremos notificando acerca de futuros cambios.')
                    ->line('Agradecemos tu paciencia en este proceso.')
                    ->line('Favor de presionar el siguiente botón, el cual te redireccionará a nuestra página,
                                    confirmándonos que tu correo se encuentra activo.')
                    ->action('Presióname', $this->url)
                    ->line('Te esperamos, por favor no responder a este correo)')
                    ->line('Si no creaste la cuenta, por favor, haz caso omiso.');
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
