@extends('admin.layouts.app')

@section('title', 'Изменить разрешение')

@section('content')
    @include('admin.components.page-header', [
        'title' => 'Изменить разрешение',
        'subtitle' => $permission['name'],
    ])

    @component('admin.components.card')
        <form method="POST" action="{{ route('admin.permissions.update', $permission['id']) }}" class="space-y-6 p-6">
            @csrf
            @method('PUT')

            @include('admin.components.input', [
                'name' => 'name',
                'label' => 'Название разрешения',
                'required' => true,
                'value' => $permission['name'],
                'autofocus' => true,
            ])

            @include('admin.components.checkbox-group', [
                'name' => 'roles',
                'label' => 'Роли',
                'options' => array_combine($roles->toArray(), $roles->toArray()),
                'selected' => $permission['roles']->toArray(),
            ])

            <div class="flex items-center gap-3 pt-2">
                @include('admin.components.button', ['slot' => 'Сохранить', 'type' => 'submit'])
                @include('admin.components.button', [
                    'href' => route('admin.permissions.index'),
                    'variant' => 'secondary',
                    'slot' => 'Отмена',
                ])
            </div>
        </form>
    @endcomponent
@endsection
