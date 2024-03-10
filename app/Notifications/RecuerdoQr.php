<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RecuerdoQr extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $pdf;
    public function __construct($pdf)
    {
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
    public function toMail(object $notifiable): MailMessage{
        return (new MailMessage)
        ->subject('Recordatorio del Congreso Próximo')
        ->greeting('Estimado/a '.$notifiable->nombres.',')
        ->line('Queremos recordarte que nuestro VI Congreso Internacional & XXII Nacional de Enfermería Quirúrgica está a punto de comenzar. Para acceder al evento, asegúrate de tener tu código QR a mano, ya que será tu entrada general (asistencia ) y asistencia de talleres (si, te encuentras inscrito a alguno).')
        ->line('La constancia general de asistencia al congreso será digital y estará disponible para su descarga en nuestro portal "https://aepeq.mx" (se notificará cuando esté disponible).')
        ->line('Si aún no te has inscrito para presentar algún trabajo académico, fotografía o representar a algún estado de la República Mexicana en el apartado de traje típico, puedes hacerlo siguiendo las indicaciones en los siguientes enlaces: "https://aepeq.mx/actividades#resumenes", "https://aepeq.mx/actividades#fotografia", "https://aepeq.mx/actividades#traje-tipico".')
        ->line('Adjunto a este correo encontrarás tu código QR de acceso para mayor comodidad.')
        ->line('Para obtener más información, no dudes en comunicarte con nosotros a través de nuestro WhatsApp: +52 444 438 9723.')
        ->action('Página Principal', 'https://aepeq.mx')
        ->line('Te esperamos y agradecemos que no respondas a este correo. ¡Nos vemos en el evento!')
        ->salutation('Atentamente, Equipo AEPEQ')
        ->attachData($this->pdf->output(), 'acceso_'.$notifiable->id.'.pdf', [
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
