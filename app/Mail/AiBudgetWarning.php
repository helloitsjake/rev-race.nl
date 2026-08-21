<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AiBudgetWarning extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public float $spentUsd,
        public float $budgetUsd,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('RevRace: AI-dagbudget %.0f%% verbruikt', $this->budgetUsd > 0 ? ($this->spentUsd / $this->budgetUsd) * 100 : 0),
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.ai-budget-warning',
        );
    }
}
