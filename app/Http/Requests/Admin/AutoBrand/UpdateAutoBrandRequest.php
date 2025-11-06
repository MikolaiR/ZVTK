<?php

namespace App\Http\Requests\Admin\AutoBrand;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAutoBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $brand = $this->route('brand');
        $brandId = is_object($brand) ? $brand->getKey() : (int) $brand;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('auto_brands', 'name')->ignore($brandId)],
        ];
    }
}
