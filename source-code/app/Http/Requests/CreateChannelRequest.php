<?php

namespace App\Http\Requests;

use App\Models\Channel\Casts\ChannelRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateChannelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "name" => "required|string|max:255",
            "description" => "nullable|string|max:255",
            'role' => ['sometimes', Rule::enum(ChannelRole::class)]
        ];
    }
}
