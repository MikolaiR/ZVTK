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
            'name' => ['required', 'string', 'max:255'],
            'auto_brand_id' => ['required', 'string', 'exists:auto_brands,id'],
        ];
    }
}
