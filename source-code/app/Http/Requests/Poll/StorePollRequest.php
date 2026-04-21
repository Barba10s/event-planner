<?php

namespace App\Http\Requests\Poll;

use Illuminate\Foundation\Http\FormRequest;

class StorePollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'allow_multiple_votes' => ['boolean'],
            'ends_at' => ['nullable', 'date', 'after:now'],
            'options' => ['required', 'array', 'min:2', 'max:10'],
            'options.*' => ['required', 'string', 'max:100', 'distinct'],
        ];
    }
}
