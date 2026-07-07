@extends('admin.layouts.app')

@section('title', 'Добавить компанию')

@section('content')
    @include('admin.components.page-header', [
        'title' => 'Добавить компанию',
        'subtitle' => 'Создание новой компании',
    ])

    @component('admin.components.card')
        <form method="POST" action="{{ route('admin.companies.store') }}" class="space-y-6 p-6">
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
                    'href' => route('admin.companies.index'),
                    'variant' => 'secondary',
                    'slot' => 'Отмена',
                ])
            </div>
        </form>
    @endcomponent
@endsection
