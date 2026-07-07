@extends('admin.layouts.app')

@section('title', 'Изменить пользователя')

@section('content')
    @include('admin.components.page-header', [
        'title' => 'Изменить пользователя',
        'subtitle' => $user['name'],
    ])

    @component('admin.components.card')
        <form method="POST" action="{{ route('admin.users.update', $user['id']) }}" class="space-y-6 p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                @include('admin.components.input', [
                    'name' => 'name',
                    'label' => 'Имя',
                    'required' => true,
                    'value' => $user['name'],
                    'autofocus' => true,
                ])

                @include('admin.components.input', [
                    'name' => 'email',
                    'label' => 'Email',
                    'type' => 'email',
                    'required' => true,
                    'value' => $user['email'],
                ])

                @include('admin.components.input', [
                    'name' => 'password',
                    'label' => 'Пароль (оставьте пустым, чтобы не менять)',
                    'type' => 'password',
                ])

                @include('admin.components.select', [
                    'name' => 'company_id',
                    'label' => 'Компания',
                    'placeholder' => 'Без компании',
                    'options' => $companies->pluck('name', 'id')->toArray(),
                    'value' => $user['company_id'] ?? '',
                ])
            </div>

            <div class="flex items-center gap-2">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    {{ old('is_active', $user['is_active']) ? 'checked' : '' }}
                    id="is_active"
                    class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-600"
                />
                <label for="is_active" class="text-sm font-medium text-slate-700">Активен</label>
            </div>

            @include('admin.components.checkbox-group', [
                'name' => 'roles',
                'label' => 'Роли',
                'options' => array_combine($roles->toArray(), $roles->toArray()),
                'selected' => $user['roles']->toArray(),
            ])

            @include('admin.components.checkbox-group', [
                'name' => 'permissions',
                'label' => 'Дополнительные разрешения',
                'options' => array_combine($permissions->toArray(), $permissions->toArray()),
                'selected' => $user['permissions']->toArray(),
            ])

            <div class="flex items-center gap-3 pt-2">
                @include('admin.components.button', ['slot' => 'Сохранить', 'type' => 'submit'])
                @include('admin.components.button', [
                    'href' => route('admin.users.index'),
                    'variant' => 'secondary',
                    'slot' => 'Отмена',
                ])
            </div>
        </form>
    @endcomponent
@endsection
