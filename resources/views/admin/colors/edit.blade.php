@extends('admin.layouts.app')

@section('title', 'Изменить цвет')

@section('content')
    @include('admin.components.page-header', [
        'title' => 'Изменить цвет',
        'subtitle' => $color['name'],
    ])

    @component('admin.components.card')
        <form method="POST" action="{{ route('admin.colors.update', $color['id']) }}" class="space-y-6 p-6">
            @csrf
            @method('PUT')

            @include('admin.components.input', [
                'name' => 'name',
                'label' => 'Название',
                'required' => true,
                'value' => $color['name'],
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
