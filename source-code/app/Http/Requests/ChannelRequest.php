<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChannelRequest extends FormRequest
{
    public function authorize()
    {
        return backpack_auth()->check();
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'min:3', 'max:1000'],
            'owner_id' => ['required', 'exists:users,id'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Название канала обязательно',
            'name.min' => 'Название должно быть не менее 3 символов',
            'name.max' => 'Название не может превышать 255 символов',
            'description.min' => 'Описание должно быть не менее 3 символов',
            'description.max' => 'Описание не может превышать 1000 символов',
            'owner_id.required' => 'Выберите владельца',
            'owner_id.exists' => 'Указанный пользователь не существует',
        ];
    }
}
