<?php

namespace App\Models;

use App\Models\Channel\Channel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'created_by',
        'question',
        'description',
        'allow_multiple_votes',
        'ends_at',
        'published_at',
    ];

    protected $casts = [
        'allow_multiple_votes' => 'boolean',
        'ends_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            });
    }

    public function scopeActive($query)
    {
        return $query->published()
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            });
    }

    public function isActive(): bool
    {
        return $this->published_at !== null &&
            ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function isClosed(): bool
    {
        return $this->ends_at !== null && $this->ends_at->isPast();
    }
}
