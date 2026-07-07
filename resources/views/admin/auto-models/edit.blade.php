@extends('admin.layouts.app')

@section('title', 'Изменить модель')

@section('content')
    @include('admin.components.page-header', [
        'title' => 'Изменить модель',
        'subtitle' => $model['name'],
    ])

    @component('admin.components.card')
        <form method="POST" action="{{ route('admin.auto.models.update', $model['id']) }}" class="space-y-6 p-6">
            @csrf
            @method('PUT')

            @include('admin.components.select', [
                'name' => 'auto_brand_id',
                'label' => 'Марка',
                'required' => true,
                'placeholder' => 'Выберите марку',
                'options' => $brands->pluck('name', 'id')->toArray(),
                'value' => $model['auto_brand_id'],
            ])

            @include('admin.components.input', [
                'name' => 'name',
                'label' => 'Название модели',
                'required' => true,
                'value' => $model['name'],
                'autofocus' => true,
            ])

            <div class="flex items-center gap-3 pt-2">
                @include('admin.components.button', ['slot' => 'Сохранить', 'type' => 'submit'])
                @include('admin.components.button', [
                    'href' => route('admin.auto.models.index'),
                    'variant' => 'secondary',
                    'slot' => 'Отмена',
                ])
            </div>
        </form>
    @endcomponent
@endsection
