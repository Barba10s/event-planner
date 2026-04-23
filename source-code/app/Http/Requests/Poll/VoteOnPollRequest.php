<?php

namespace App\Http\Requests\Poll;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VoteOnPollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $poll = $this->route('poll');
        $optionIds = $poll?->options->pluck('id')->toArray() ?? [];

        return [
            'option_id' => ['required', 'array', 'min:1'],
            'option_id.*' => ['required', Rule::in($optionIds)],
        ];
    }
}
