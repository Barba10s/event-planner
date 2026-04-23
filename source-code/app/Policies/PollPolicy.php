<?php

namespace App\Policies;

use App\Models\Poll;
use App\Models\User;

class PollPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Poll $poll): bool
    {
        if ($poll->relationLoaded('channel') && $poll->channel->relationLoaded('members')) {
            return $poll->channel->members->contains('id', $user->id);
        }

        return $poll->channel->members()->where('user_id', $user->id)->exists();
    }

    public function create(User $user, Poll $poll): bool
    {
        if (!$poll->relationLoaded('channel')) {
            $poll->load('channel');
        }

        return $poll->channel?->owner_id === $user->id;
    }

    public function update(User $user, Poll $poll): bool
    {
        return $poll->channel->owner_id === $user->id || $poll->created_by === $user->id;
    }

    public function delete(User $user, Poll $poll): bool
    {
        return $poll->channel->owner_id === $user->id || $poll->created_by === $user->id;
    }

    public function vote(User $user, Poll $poll): bool
    {
        if (!$poll->isActive()) {
            return false;
        }

        if (!$poll->relationLoaded('channel')) {
            $poll->load('channel');
        }

        $isMember = $poll->channel->members->contains('id', $user->id);
        if (!$isMember) {
            return false;
        }

        if ($poll->allow_multiple_votes) {
            return true;
        }

        return true;
    }

    public function viewResults(User $user, Poll $poll): bool
    {
        return $this->view($user, $poll);
    }
}
