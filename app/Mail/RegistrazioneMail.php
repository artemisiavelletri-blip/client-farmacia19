<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrazioneMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct()
    {

    }

    public function build()
    {
        return $this->subject('Registrazione Farmacia19')
                    ->view('emails.register');
    }
}