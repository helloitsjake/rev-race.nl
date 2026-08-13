<?php

namespace App\Mail;

use App\Models\MotorReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MotorReportReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MotorReport $report,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Melding motordata: {$this->report->motor->label()}",
            replyTo: $this->report->reporter_email ? [$this->report->reporter_email] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.motor-report',
        );
    }
}
