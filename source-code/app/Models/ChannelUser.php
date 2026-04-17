<?php

namespace App\Models;

use App\Models\Channel\Casts\ChannelRole;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ChannelUser extends Pivot
{
    protected $casts = [
        'role' => ChannelRole::class,
        'invited_at' => 'datetime',
        'joined_at' => 'datetime',
    ];
}
