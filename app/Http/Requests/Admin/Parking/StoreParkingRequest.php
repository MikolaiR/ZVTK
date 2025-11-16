<?php

namespace App\Http\Requests\Admin\Parking;

use Illuminate\Foundation\Http\FormRequest;

class StoreParkingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();
        if (array_key_exists('company_id', $data) && ($data['company_id'] === '' || $data['company_id'] === 'null')) {
            $this->merge(['company_id' => null]);
        }
    }
}
