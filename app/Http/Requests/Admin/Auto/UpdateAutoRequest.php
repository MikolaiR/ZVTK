<?php

namespace App\Http\Requests\Admin\Auto;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Auto as AutoModel;
use App\Enums\Statuses;

class UpdateAutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var AutoModel $auto */
        $auto = $this->route('auto');
        $statusValues = implode(',', array_map(fn($c) => $c->value, Statuses::cases()));

        return [
            'title' => ['nullable', 'string', 'max:255'],
            'auto_brand_id' => ['required', 'string', 'exists:auto_brands,id'],
            'auto_model_id' => ['required', 'integer', 'exists:auto_models,id'],
            'color_id' => ['nullable', 'integer', 'exists:colors,id'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'sender_id' => ['nullable', 'integer', 'exists:senders,id'],
            'provider_id' => ['nullable', 'integer', 'exists:providers,id'],
            'vin' => [
                'required', 'string', 'max:255',
                Rule::unique('autos', 'vin')->ignore($auto->id),
            ],
            'departure_date' => ['nullable', 'date'],
            'year' => ['nullable', 'integer', 'digits:4', 'min:1900', 'max:'.date('Y')],
            'price' => ['nullable', 'integer', 'min:0'],
            'status' => ["nullable", "integer", "in:$statusValues"],

            'photos' => ['array'],
            'photos.*' => ['file', 'mimetypes:image/jpeg,image/png,image/webp'],
            'videos' => ['array'],
            'videos.*' => ['file', 'mimetypes:video/mp4,video/quicktime,video/x-msvideo'],
            'documents' => ['array'],
            'documents.*' => ['file', 'mimetypes:application/pdf,image/jpeg,image/png'],

            'remove_media' => ['array'],
            'remove_media.*' => ['integer'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $nullableIntegerKeys = ['color_id', 'company_id', 'sender_id', 'provider_id'];
        $data = $this->all();
        foreach ($nullableIntegerKeys as $key) {
            if (array_key_exists($key, $data) && ($data[$key] === '' || $data[$key] === 'null')) {
                $this->merge([$key => null]);
            }
        }
    }
}
