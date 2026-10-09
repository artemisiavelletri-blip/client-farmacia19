<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderDeliveredMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Crea una nuova email di consegna.
     */
    public function __construct(
        public Order $order
    ) {
        // Carica i dati dell'utente associato all'ordine
        $this->order->loadMissing('user');
    }

    /**
     * Oggetto dell'email.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Il tuo ordine #' .
                $this->order->order_number .
                ' è stato consegnato - Farmacia19.it'
        );
    }

    /**
     * Template HTML dell'email.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.orderComplete',
            with: [
                'order' => $this->order,
            ]
        );
    }

    /**
     * Allegati dell'email.
     */
    public function attachments(): array
    {
        return [];
    }
}
