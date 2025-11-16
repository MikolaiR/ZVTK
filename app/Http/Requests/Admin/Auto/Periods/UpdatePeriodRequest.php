<?php

namespace App\Http\Requests\Admin\Auto\Periods;

use App\Enums\Statuses;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePeriodRequest extends FormRequest
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
            'started_at' => ['required', 'date'],
            'ended_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
            'accepted_by_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
