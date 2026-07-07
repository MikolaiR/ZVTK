@extends('admin.layouts.app')

@section('title', 'Изменить роль')

@section('content')
    @include('admin.components.page-header', [
        'title' => 'Изменить роль',
        'subtitle' => $role['name'],
    ])

    @component('admin.components.card')
        <form method="POST" action="{{ route('admin.roles.update', $role['id']) }}" class="space-y-6 p-6">
            @csrf
            @method('PUT')

            @include('admin.components.input', [
                'name' => 'name',
                'label' => 'Название роли',
                'required' => true,
                'value' => $role['name'],
                'autofocus' => true,
            ])

            @include('admin.components.checkbox-group', [
                'name' => 'permissions',
                'label' => 'Разрешения',
                'options' => array_combine($permissions->toArray(), $permissions->toArray()),
                'selected' => $role['permissions']->toArray(),
            ])

            <div class="flex items-center gap-3 pt-2">
                @include('admin.components.button', ['slot' => 'Сохранить', 'type' => 'submit'])
                @include('admin.components.button', [
                    'href' => route('admin.roles.index'),
                    'variant' => 'secondary',
                    'slot' => 'Отмена',
                ])
            </div>
        </form>
    @endcomponent
@endsection
