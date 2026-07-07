@extends('admin.layouts.app')

@section('title', 'Добавить роль')

@section('content')
    @include('admin.components.page-header', [
        'title' => 'Добавить роль',
        'subtitle' => 'Создание новой роли и назначение разрешений',
    ])

    @component('admin.components.card')
        <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-6 p-6">
            @csrf

            @include('admin.components.input', [
                'name' => 'name',
                'label' => 'Название роли',
                'required' => true,
                'autofocus' => true,
            ])

            @include('admin.components.checkbox-group', [
                'name' => 'permissions',
                'label' => 'Разрешения',
                'options' => array_combine($permissions->toArray(), $permissions->toArray()),
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
