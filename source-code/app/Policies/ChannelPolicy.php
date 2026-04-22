<?php

namespace App\Policies;

use App\Models\Channel\Channel;
use App\Models\User;

class ChannelPolicy
{
    public function view(User $user, Channel $channel): bool
    {
        if ($channel->owner_id === $user->id) {
            return true;
        }

        if ($channel->relationLoaded('members')) {
            return $channel->members->contains('id', $user->id);
        }

        return $channel->members()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Channel $channel): bool
    {
        return $channel->owner_id === $user->id;
    }

    public function delete(User $user, Channel $channel): bool
    {
        return $channel->owner_id === $user->id;
    }

    public function join(User $user, Channel $channel): bool
    {
        if ($channel->relationLoaded('members')) {
            $isMember = $channel->members->contains('id', $user->id);
            return $channel->owner_id !== $user->id && !$isMember;
        }

        return $channel->owner_id !== $user->id &&
            !$channel->members()->where('user_id', $user->id)->exists();
    }

    public function manageMembers(User $user, Channel $channel): bool
    {
        return $channel->owner_id === $user->id;
    }
}
