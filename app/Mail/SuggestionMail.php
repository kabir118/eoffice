<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SuggestionMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $senderName;
    public string $senderEmail;
    public string $senderDesignation;
    public string $senderDepartment;
    public string $subject;
    public string $messageBody;

    public function __construct(
        string $senderName,
        string $senderEmail,
        string $senderDesignation,
        string $senderDepartment,
        string $subject,
        string $messageBody
    ) {
        $this->senderName        = $senderName;
        $this->senderEmail       = $senderEmail;
        $this->senderDesignation = $senderDesignation;
        $this->senderDepartment  = $senderDepartment;
        $this->subject           = $subject;
        $this->messageBody       = $messageBody;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[e-Office] Suggestion / Issue: ' . $this->subject,
            replyTo: [$this->senderEmail],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.suggestion',
        );
    }
}
