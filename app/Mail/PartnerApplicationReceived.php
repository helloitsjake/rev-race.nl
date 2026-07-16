<?php

namespace App\Mail;

use App\Models\PartnerApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnerApplicationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PartnerApplication $application,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nieuwe partneraanmelding: {$this->application->company_name}",
            replyTo: [$this->application->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.partner-application',
        );
    }
}
