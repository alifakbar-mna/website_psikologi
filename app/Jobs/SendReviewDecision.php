<?php

namespace App\Jobs;

use App\Mail\ReviewDecision;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendReviewDecision implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(
        public string $email,
        public string $title,
        public string $status,
        public ?string $note
    ) {
        //
    }

    public function backoff(): array
    {
        return [
            60,
            300,
            900,
            3600,
        ];
    }

    public function handle(): void
    {
        Mail::to($this->email)->send(
            new ReviewDecision(
                $this->title,
                $this->status,
                $this->note
            )
        );
    }
}