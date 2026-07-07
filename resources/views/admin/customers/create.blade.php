@extends('admin.layouts.app')

@section('title', 'Добавить таможню')

@section('content')
    @include('admin.components.page-header', [
        'title' => 'Добавить таможню',
        'subtitle' => 'Создание новой таможенной организации',
    ])

    @component('admin.components.card')
        <form method="POST" action="{{ route('admin.customers.store') }}" class="space-y-6 p-6">
            @csrf

            @include('admin.components.input', [
                'name' => 'name',
                'label' => 'Название',
                'required' => true,
                'autofocus' => true,
            ])

            @include('admin.components.input', [
                'name' => 'phone',
                'label' => 'Телефон',
            ])

            @include('admin.components.input', [
                'name' => 'email',
                'label' => 'Email',
                'type' => 'email',
            ])

            @include('admin.components.input', [
                'name' => 'document_number',
                'label' => 'Номер документа',
            ])

            <div class="flex items-center gap-3 pt-2">
                @include('admin.components.button', ['slot' => 'Сохранить', 'type' => 'submit'])
                @include('admin.components.button', [
                    'href' => route('admin.customers.index'),
                    'variant' => 'secondary',
                    'slot' => 'Отмена',
                ])
            </div>
        </form>
    @endcomponent
@endsection
