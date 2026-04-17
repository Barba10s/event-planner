<?php

namespace App\Policies;

use App\Models\Channel;
use App\Models\User;

class ChannelPolicy
{
    public function view(User $user, Channel $channel): bool
    {
        return $channel->owner_id === $user->id ||
            $channel->members->contains($user->id);
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
        return $channel->owner_id !== $user->id &&
            !$channel->members->contains($user->id);
    }
    
    public function manageMembers(User $user, Channel $channel): bool
    {
        return $channel->owner_id === $user->id ||
            $channel->members()->wherePivot('user_id', $user->id)->wherePivot('role', 'admin')->exists();
    }
}
