@extends('admin.layouts.app')

@section('title', 'Добавить разрешение')

@section('content')
    @include('admin.components.page-header', [
        'title' => 'Добавить разрешение',
        'subtitle' => 'Создание нового разрешения и назначение ролей',
    ])

    @component('admin.components.card')
        <form method="POST" action="{{ route('admin.permissions.store') }}" class="space-y-6 p-6">
            @csrf

            @include('admin.components.input', [
                'name' => 'name',
                'label' => 'Название разрешения',
                'required' => true,
                'autofocus' => true,
            ])

            @include('admin.components.checkbox-group', [
                'name' => 'roles',
                'label' => 'Роли',
                'options' => array_combine($roles->toArray(), $roles->toArray()),
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
