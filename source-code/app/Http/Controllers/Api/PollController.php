<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Poll\StorePollRequest;
use App\Http\Requests\Poll\VoteOnPollRequest;
use App\Http\Resources\Poll\PollResource;
use App\Jobs\SendPollCreatedNotifications;
use App\Models\Channel\Channel;
use App\Models\Poll;
use App\Models\Vote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PollController extends Controller
{
    public function store(StorePollRequest $request, int $channelId): JsonResponse
    {
        $channel = $this->getChannelForUser($request, $channelId);

        $this->authorize('create', new Poll(['channel' => $channel]));

        $poll = DB::transaction(function () use ($request, $channel) {
            $poll = $channel->polls()->create([
                'created_by' => $request->user()->id,
                'question' => $request->question,
                'description' => $request->description,
                'allow_multiple_votes' => $request->boolean('allow_multiple_votes', false),
                'ends_at' => $request->ends_at,
                'published_at' => now(),
            ]);

            foreach ($request->options as $text) {
                $poll->options()->create(['text' => $text]);
            }
            return $poll->load('options');
        });

        SendPollCreatedNotifications::dispatch($poll->id)->afterCommit();

        return response()->json([
            'success' => true,
            'poll' => PollResource::make($poll),
        ], 201);
    }

    public function index(Request $request, int $channelId): JsonResponse
    {
        $channel = $this->getChannelForUser($request, $channelId);
        $this->authorize('viewAny', Poll::class);

        $polls = $channel->polls()
            ->published()
            ->with(['creator', 'options'])
            ->latest('published_at')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => PollResource::collection($polls),
            'meta' => [
                'current_page' => $polls->currentPage(),
                'total' => $polls->total(),
            ],
        ]);
    }

    public function vote(VoteOnPollRequest $request, int $channelId, int $pollId): JsonResponse
    {
        $channel = $this->getChannelForUser($request, $channelId);
        $poll = $channel->polls()->with('options')->findOrFail($pollId);

        $this->authorize('vote', $poll);

        $optionIds = $request->validated('option_id');

        DB::transaction(function () use ($poll, $request, $optionIds) {
            foreach ($optionIds as $optionId) {
                Vote::firstOrCreate([
                    'poll_id' => $poll->id,
                    'user_id' => $request->user()->id,
                    'option_id' => $optionId,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Vote recorded successfully',
            'poll' => PollResource::make($poll->load('options.votes')),
        ]);
    }

    public function results(Request $request, int $channelId, int $pollId): JsonResponse
    {
        $channel = $this->getChannelForUser($request, $channelId);
        $poll = $channel->polls()->with(['options.votes'])->findOrFail($pollId);

        $this->authorize('viewResults', $poll);

        $totalVotes = $poll->votes()->count();

        $options = $poll->options->map(function ($option) use ($totalVotes) {
            $count = $option->votes->count();
            return [
                'id' => $option->id,
                'text' => $option->text,
                'votes' => $count,
                'percentage' => $totalVotes > 0 ? round(($count / $totalVotes) * 100, 1) : 0.0,
            ];
        });

        return response()->json([
            'success' => true,
            'poll_id' => $poll->id,
            'total_votes' => $totalVotes,
            'options' => $options->values(),
            'is_active' => $poll->isActive(),
        ]);
    }

    private function getChannelForUser(Request $request, int $channelId): Channel
    {
        return $request->user()->channels()->findOrFail($channelId);
    }
}
