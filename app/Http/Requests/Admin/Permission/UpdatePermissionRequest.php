<?php

namespace App\Http\Requests\Admin\Permission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission as SpatiePermission;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var SpatiePermission $permission */
        $permission = $this->route('permission');

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('permissions', 'name')->where('guard_name', 'web')->ignore($permission->id),
            ],
            'roles' => ['array'],
            'roles.*' => [
                'string',
                Rule::exists('roles', 'name')->where('guard_name', 'web'),
            ],
        ];
    }
}
