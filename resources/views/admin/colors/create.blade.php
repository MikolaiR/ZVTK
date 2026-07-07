@extends('admin.layouts.app')

@section('title', 'Добавить цвет')

@section('content')
    @include('admin.components.page-header', [
        'title' => 'Добавить цвет',
        'subtitle' => 'Создание нового цвета',
    ])

    @component('admin.components.card')
        <form method="POST" action="{{ route('admin.colors.store') }}" class="space-y-6 p-6">
            @csrf

            @include('admin.components.input', [
                'name' => 'name',
                'label' => 'Название',
                'required' => true,
                'autofocus' => true,
            ])

            <div class="flex items-center gap-3 pt-2">
                @include('admin.components.button', ['slot' => 'Сохранить', 'type' => 'submit'])
                @include('admin.components.button', [
                    'href' => route('admin.colors.index'),
                    'variant' => 'secondary',
                    'slot' => 'Отмена',
                ])
            </div>
        </form>
    @endcomponent
@endsection
