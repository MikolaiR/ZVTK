<?php

namespace App\Http\Requests\Admin\AutoBrand;

use Illuminate\Foundation\Http\FormRequest;

class StoreAutoBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:auto_brands,name'],
        ];
    }
}
