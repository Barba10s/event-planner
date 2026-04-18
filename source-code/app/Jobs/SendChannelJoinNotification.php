<?php

namespace App\Jobs;

use App\Models\Channel\Channel;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendChannelJoinNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];
    public int $timeout = 30;

    public function __construct(
        public readonly int $channelId,
        public readonly int $newMemberId,
    ) {
    }

    public function handle(): void
    {
        $channel = Channel::find($this->channelId);
        $user = User::find($this->newMemberId);

        if (!$channel || !$user) {
            Log::warning('Channel or user not found for notification', [
                'channel_id' => $this->channelId,
                'user_id' => $this->newMemberId,
            ]);
            return;
        }

        Log::info(" User {$user->name} joined channel: {$channel->name}");
    }

    public function failed(Throwable $exception): void
    {
        Log::error("Job failed: SendChannelJoinNotification for channel {$this->channelId}", [
            'error' => $exception->getMessage(),
        ]);
    }
}
