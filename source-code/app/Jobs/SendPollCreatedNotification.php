<?php

namespace App\Jobs;

use App\Mail\PollCreatedMail;
use App\Models\Poll;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Log;
use Throwable;

class SendPollCreatedNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];
    public int $timeout = 60;

    public function __construct(
        public readonly int $pollId,
    ) {
    }

    public function handle(): void
    {
        $poll = Poll::with(['channel.members', 'creator'])->find($this->pollId);

        if (!$poll || !$poll->isActive()) {
            return;
        }

        foreach ($poll->channel->members as $member) {
            if ($member->id === $poll->created_by) {
                continue;
            }

            Mail::to($member->email)->send(new PollCreatedMail($poll, $member));
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Failed to send poll notifications', [
            'poll_id' => $this->pollId,
            'error' => $exception->getMessage(),
        ]);
    }
}
