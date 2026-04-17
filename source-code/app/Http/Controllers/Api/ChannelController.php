<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateChannelRequest;
use App\Http\Resources\ChannelResource;
use App\Models\Channel\Casts\ChannelRole;
use App\Models\Channel\Channel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChannelController extends Controller
{

    use AuthorizesRequests;

    public function store(CreateChannelRequest $request): JsonResponse
    {
        $channel = $request->user()->ownedChannels()->create([
            "name" => $request->name,
            "description" => $request->description,
            "invite_token" => Str::random(32),
        ]);

        $channel->members()->attach($request->user()->id, [
            'role' => ChannelRole::OWNER,
            'joined_at' => now(),
        ]);

        return response()->json([
            "success" => true,
            "channel" => channelResource::make($channel)
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $channels = Channel::accessibleBy($userId)
            ->with(['owner', 'members'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => ChannelResource::collection($channels),
            'meta' => [
                'current_page' => $channels->currentPage(),
                'total' => $channels->total(),
                'per_page' => $channels->perPage(),
            ],
        ]);
    }

    public function show(Request $request, Channel $channel): JsonResponse
    {
        $this->authorize('view', $channel);

        return response()->json([
            'success' => true,
            'channel' => ChannelResource::make($channel->load(['owner', 'members'])),
        ]);
    }

    public function joinByToken(Request $request, string $token): JsonResponse
    {
        $channel = Channel::byInviteToken($token)->firstOrFail();
        $this->authorize('join', $channel);

        $channel->members()->syncWithoutDetaching([
            $request->user()->id => [
                'role' => ChannelRole::MEMBER,
                'invited_at' => now(),
                'joined_at' => now(),
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully joined the channel',
            'channel' => ChannelResource::make($channel->fresh()),
        ]);
    }
}
