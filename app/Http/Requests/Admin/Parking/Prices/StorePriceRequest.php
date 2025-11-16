<?php

namespace App\Http\Requests\Admin\Parking\Prices;

use Illuminate\Foundation\Http\FormRequest;

class StorePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'integer', 'min:0'],
            'date_start' => ['required', 'date'],
            'date_end' => ['nullable', 'date', 'after_or_equal:date_start'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();
        if (array_key_exists('date_end', $data) && ($data['date_end'] === '' || $data['date_end'] === 'null')) {
            $this->merge(['date_end' => null]);
        }
    }
}
