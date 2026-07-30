<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FileForwardedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Document $document;
    public string $remarks;
    public string $senderName;

    /**
     * Create a new message instance.
     */
    public function __construct(Document $document, string $remarks = '', string $senderName = '')
    {
        $this->document = $document;
        $this->remarks = $remarks;
        $this->senderName = $senderName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New File/ Task Assigned',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.documents.forwarded',
        );
    }
}
