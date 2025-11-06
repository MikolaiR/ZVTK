<?php

namespace App\Http\Requests\Admin\AutoModel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAutoModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255',
                Rule::unique('auto_models', 'name')->where(function ($q) {
                    return $q->where('auto_brand_id', (int) $this->input('auto_brand_id'));
                }),
            ],
            'auto_brand_id' => ['required', 'integer', 'exists:auto_brands,id'],
        ];
    }
}
