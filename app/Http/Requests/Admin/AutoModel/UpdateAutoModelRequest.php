<?php

namespace App\Http\Requests\Admin\AutoModel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAutoModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $model = $this->route('model');
        $modelId = is_object($model) ? $model->getKey() : (int) $model;
        $brandId = (int) $this->input('auto_brand_id');

        return [
            'name' => ['required', 'string', 'max:255',
                Rule::unique('auto_models', 'name')
                    ->where(function ($q) use ($brandId) {
                        return $q->where('auto_brand_id', $brandId);
                    })
                    ->ignore($modelId),
            ],
            'auto_brand_id' => ['required', 'integer', 'exists:auto_brands,id'],
        ];
    }
}
