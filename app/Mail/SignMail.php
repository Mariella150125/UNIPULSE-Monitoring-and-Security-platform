<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class SignMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $token; // 

    public function __construct(User $user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Créez votre mot de passe - Bienvenue',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.validate',
        );
    }
}