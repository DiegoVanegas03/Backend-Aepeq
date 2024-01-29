<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CambioEnPrograma extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $campo;
    public $valor;
    public function __construct($campo , $valor)
    {
        $this->campo = strtoupper($campo);
        $this->valor = $valor;
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
        $url = 'https://aepeq.mx/programa';
        return (new MailMessage)
                    ->subject('Cambio en el Programa del congreso')
                    ->greeting('Hola  '.$notifiable->nombres)
                    ->line('Somos el equipo de AEPEQ, nos comunicamos para notificarte que hemos realizado un cambio en el programa del congreso.')
                    ->line('El cambio realizado fue el siguiente:')
                    ->line('El campo '.$this->campo.' ahora tiene el valor de '.$this->valor)
                    ->line('Para mas información visitanos en nuestra página web')
                    ->action('IR A PROGRAMA', $url)
                    ->line('Te invitamos si todavia nos estas inscrit@ a que revises nuestros talleres y actividades son totalmente gratuitas y puedes inscribirte en ellas desde nuestra página web.')
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
