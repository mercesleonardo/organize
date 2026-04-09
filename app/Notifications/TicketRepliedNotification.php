<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketRepliedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('dashboard', absolute: true);

        return (new MailMessage())
            ->subject('Resposta ao seu chamado — ' . $this->ticket->subject)
            ->markdown('mail.tickets.replied', [
                'ticket'       => $this->ticket,
                'dashboardUrl' => $url,
                'userName'     => $notifiable->name,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'subject'   => $this->ticket->subject,
            'body'      => 'O suporte respondeu ao seu chamado.',
        ];
    }
}
