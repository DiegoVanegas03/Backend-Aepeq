<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordReset extends Notification
{
    use Queueable;
    public $url;

    /**
     * Create a new notification instance.
     */
    public function __construct( $url)
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
    public function toMail($notifiable){
        return (new MailMessage)
            ->subject('Cambio de contraseña cuenta AEPEQ')
            ->greeting('¡Hola'. $notifiable->nombres. '!')
            ->line('Estas recibiendo esto ya que se solicito un cambio contraseña de parte de esta direccion electronica.')
            ->line('Expira en 1 hora')
            ->action('Cambia tu contraseña aqui', $this->url)
            ->line('Si tu no solicitaste este cambio, por favor ignora este mensaje.');
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
