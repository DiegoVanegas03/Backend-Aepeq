<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendTicketRegistroPromotor extends Notification
{
    use Queueable;

    public $subject;
    public $name;
    public $pdf;

    /**
     * Create a new notification instance.
     */
    public function __construct($subject, $name, $pdf)
    {
        $this->subject = $subject;
        $this->name = $name;
        $this->pdf = $pdf;
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
                    ->subject($this->subject)
                    ->greeting('¡Hola '. $this->name . '!')
                    ->line('Este correo es de vital importancia que lo guarde, ya que es su comprobante de registro.')
                    ->line('En el archivo adjunto entrontra los detalles del registro.')
                    ->line('Gracias por la paciencia en el proceso de registro, le deseamos un excelente día.')
                    ->salutation('Saludos de parte de todo el equipo de AEPEQ.')
                    ->attachData($this->pdf, $this->name . '_ticket.pdf', [
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
