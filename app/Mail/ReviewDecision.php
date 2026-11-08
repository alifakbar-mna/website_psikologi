<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ReviewDecision extends Mailable
{
    public function __construct(
        public string $reportTitle,
        public string $decisionStatus,
        public ?string $reviewNote
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Status upload laporan: '.$this->decisionStatus
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'cms.review-email'
        );
    }
}