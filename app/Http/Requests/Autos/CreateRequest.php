<?php

namespace App\Http\Requests\Autos;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create_auto') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'departure_date' => ['nullable', 'date'],
            'auto_brand_id' => ['required', 'string', 'exists:auto_brands,id'],
            'auto_model_id' => ['required', 'integer', 'exists:auto_models,id'],
            'color_id' => ['nullable', 'integer', 'exists:colors,id'],
            'vin' => ['required', 'string', 'max:255', 'unique:autos,vin'],
            'year' => ['nullable', 'integer', 'digits:4', 'min:1900', 'max:'.date('Y')],
            'price' => ['nullable', 'integer', 'min:0'],
            'photos.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'videos.*' => ['nullable', 'file', 'mimes:mp4,webm,ogg', 'max:51200'],
            'documents.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:20480'],
        ];
    }
}
