<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RefundMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order_number;
    public $subject;

    public function __construct($order_number,$subject)
    {
        $this->order_number = $order_number;
        $this->subject = $subject;
    }

    public function build()
    {
        return $this->subject($this->subject)
                    ->view('emails.refund');
    }
}