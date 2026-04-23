<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Poll\StorePollRequest;
use App\Http\Requests\Poll\VoteOnPollRequest;
use App\Http\Resources\Poll\PollResource;
use App\Jobs\SendPollCreatedNotification;
use App\Models\Channel\Channel;
use App\Models\Poll;
use App\Models\Vote;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PollController extends Controller
{
    use AuthorizesRequests;

    public function store(StorePollRequest $request, int $channelId): JsonResponse
    {
        $channel = $this->getChannelForUser($request, $channelId);

        $poll = new Poll(['channel_id' => $channel->id]);
        $poll->setRelation('channel', $channel);
        $this->authorize('create', $poll);

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

        SendPollCreatedNotification::dispatch($poll->id)->afterCommit();

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

    public function vote(VoteOnPollRequest $request, int $channelId, Poll $poll): JsonResponse
    {
        $this->validatePollBelongsToChannel($poll, $channelId);
        $this->authorize('vote', $poll);

        $poll->load('options');

        $optionIds = $request->option_id;

        DB::transaction(function () use ($poll, $request, $optionIds) {
            if (!$poll->allow_multiple_votes) {
                Vote::where('poll_id', $poll->id)
                    ->where('user_id', $request->user()->id)
                    ->delete();
            }

            foreach ($optionIds as $optionId) {
                Vote::create([
                    'poll_id' => $poll->id,
                    'user_id' => $request->user()->id,
                    'option_id' => $optionId,
                    'voted_at' => now(),
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Vote recorded successfully',
            'poll' => PollResource::make($poll->load('options.votes')),
        ]);
    }

    public function results(Request $request, int $channelId, Poll $poll): JsonResponse
    {
        $this->validatePollBelongsToChannel($poll, $channelId);
        $poll->load(['options.votes']);

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

    private function validatePollBelongsToChannel(Poll $poll, int $channelId): void
    {
        if ($poll->channel_id !== $channelId) {
            abort(403, 'Poll does not belong to this channel');
        }
    }
}
