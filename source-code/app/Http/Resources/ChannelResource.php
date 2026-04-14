<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChannelResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'invite_link' => config('app.url') . '/invite/' . $this->invite_token,
            'owner_id' => $this->owner_id,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
