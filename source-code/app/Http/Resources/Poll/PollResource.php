<?php

namespace App\Http\Resources\Poll;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $totalVotes = $this->whenLoaded('options', function () {
            return $this->options->sum(fn($opt) => $opt->votes->count());
        });

        return [
            'id' => $this->id,
            'question' => $this->question,
            'description' => $this->description,
            'allow_multiple_votes' => $this->allow_multiple_votes,
            'ends_at' => $this->ends_at?->toISOString(),
            'published_at' => $this->published_at?->toISOString(),
            'is_active' => $this->isActive(),
            'total_votes' => $totalVotes ?? 0,
            'creator' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ],
            'options' => PollOptionResource::collection(
                $this->whenLoaded('options')
            ),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
