<?php

namespace App\Http\Requests\Admin\Auto;

use Illuminate\Foundation\Http\FormRequest;

class StoreAutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'auto_brand_id' => ['required', 'integer', 'exists:auto_brands,id'],
            'auto_model_id' => ['required', 'integer', 'exists:auto_models,id'],
            'color_id' => ['nullable', 'integer', 'exists:colors,id'],
            'vin' => ['required', 'string', 'max:255', 'unique:autos,vin'],
            'departure_date' => ['nullable', 'date'],
            'year' => ['nullable', 'integer', 'digits:4', 'min:1900', 'max:'.date('Y')],
            'price' => ['nullable', 'integer', 'min:0'],

            'photos' => ['array'],
            'photos.*' => ['file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:51200'],
            'videos' => ['array'],
            'videos.*' => ['file', 'mimetypes:video/mp4,video/quicktime,video/x-msvideo', 'max:51200'],
            'documents' => ['array'],
            'documents.*' => ['file', 'mimetypes:application/pdf,image/jpeg,image/png', 'max:51200'],
        ];
    }
}
