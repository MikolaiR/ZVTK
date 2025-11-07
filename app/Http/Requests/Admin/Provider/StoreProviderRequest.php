<?php

namespace App\Http\Requests\Admin\Provider;

use Illuminate\Foundation\Http\FormRequest;

class StoreProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
