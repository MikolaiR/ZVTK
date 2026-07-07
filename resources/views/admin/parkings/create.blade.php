@extends('admin.layouts.app')

@section('title', 'Добавить стоянку')

@section('content')
    @include('admin.components.page-header', [
        'title' => 'Добавить стоянку',
        'subtitle' => 'Создание новой стоянки',
    ])

    @component('admin.components.card')
        <form method="POST" action="{{ route('admin.parkings.store') }}" class="space-y-6 p-6">
            @csrf

            @include('admin.components.input', [
                'name' => 'name',
                'label' => 'Название',
            ])

            @include('admin.components.input', [
                'name' => 'address',
                'label' => 'Адрес',
                'required' => true,
                'autofocus' => true,
            ])

            @include('admin.components.select', [
                'name' => 'company_id',
                'label' => 'Компания',
                'placeholder' => 'Без компании',
                'options' => $companies->pluck('name', 'id')->toArray(),
            ])

            <div class="flex items-center gap-3 pt-2">
                @include('admin.components.button', ['slot' => 'Сохранить', 'type' => 'submit'])
                @include('admin.components.button', [
                    'href' => route('admin.parkings.index'),
                    'variant' => 'secondary',
                    'slot' => 'Отмена',
                ])
            </div>
        </form>
    @endcomponent
@endsection
