<?php

namespace App\Models\Channel;

use App\Models\ChannelUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'owner_id', 'invite_token'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'channel_user')
            ->using(ChannelUser::class)
            ->withPivot('role', 'invited_at', 'joined_at')
            ->withTimestamps();
    }

    public function scopeAccessibleBy($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('owner_id', $userId)
                ->orWhereHas('members', fn($m) => $m->where('user_id', $userId));
        });
    }

    public function scopeByInviteToken($query, string $token)
    {
        return $query->where('invite_token', $token);
    }
}
