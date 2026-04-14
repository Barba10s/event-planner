<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateChannelRequest;
use App\Http\Resources\ChannelResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ChannelController extends Controller
{
    public function create(CreateChannelRequest $request): JsonResponse
    {
        $channel = $request->user()->ownedChannels()->create([
            "name" => $request->name,
            "description" => $request->description,
            "invitation_token" => Str::random(32),
        ]);

        return response()->json([
            "success" => true,
            "channel" => channelResource::make($channel)
        ], 201);
    }
}
