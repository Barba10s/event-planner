<?php

namespace App\Mail;

use App\Models\Poll;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PollCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Poll $poll,
        public readonly User $recipient,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Новый опрос: ' . $this->poll->question,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.poll-created',
        );
    }
}
