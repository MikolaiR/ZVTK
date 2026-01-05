<?php

namespace App\Http\Requests\Autos;

use Illuminate\Foundation\Http\FormRequest;

class TransitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $auto = $this->route('auto');

        return $user !== null && $auto !== null && $user->can('update', $auto);
    }

    public function rules(): array
    {
        $action = (string) $this->input('action');

        $base = [
            'action' => ['required', 'string', 'in:move_to_customs,move_to_parking,accept_at_parking,move_to_customs_from_parking,move_to_other_parking,sell,save_files'],
            'note' => ['nullable', 'string', 'max:2000'],
            'photos.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'videos.*' => ['nullable', 'file', 'mimes:mp4,webm,ogg', 'max:51200'],
            'documents.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:20480'],
        ];

        $specific = [];
        switch ($action) {
            case 'move_to_customs':
            case 'move_to_customs_from_parking':
                $specific = [
                    'customer_id' => ['required', 'integer', 'exists:customers,id'],
                    'arrival_date' => ['nullable', 'date'],
                ];
                break;
            case 'move_to_parking':
            case 'move_to_other_parking':
                $specific = [
                    'parking_id' => ['required', 'integer', 'exists:parkings,id'],
                ];
                break;
            case 'accept_at_parking':
                $specific = [];
                break;
            case 'sell':
                $specific = [
                    'sold_at' => ['nullable', 'date'],
                ];
                break;
            case 'save_files':
                $specific = [];
                break;
        }

        return array_merge($base, $specific);
    }
}
