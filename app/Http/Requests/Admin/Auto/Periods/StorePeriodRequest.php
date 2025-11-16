<?php

namespace App\Http\Requests\Admin\Auto\Periods;

use App\Enums\Statuses;
use Illuminate\Foundation\Http\FormRequest;

class StorePeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $statusValues = implode(',', array_map(fn($c) => $c->value, Statuses::cases()));

        return [
            'status' => ["required", "integer", "in:$statusValues"],
            'location_id' => ['nullable', 'integer'],
            'accepted_by_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
