<?php

namespace App\Models\Channel\Casts;

enum ChannelRole: string
{
    case OWNER = 'owner';
    case MEMBER = 'member';

    public function role(): string
    {
        return match ($this) {
            self::OWNER => "Владелец",
            self::MEMBER => "Участник"
        };
    }
}

