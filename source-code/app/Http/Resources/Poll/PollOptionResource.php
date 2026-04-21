<?php

namespace App\Http\Resources\Poll;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PollOptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'votes' => $this->votes->count(),
            'percentage' => $this->when($this->relationLoaded('votes'), function () {
                $total = $this->poll->votes()->count();
                $count = $this->votes->count();
                return $total > 0 ? round(($count / $total) * 100, 1) : 0.0;
            }),
        ];
    }
}
