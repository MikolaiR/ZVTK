@extends('admin.layouts.app')

@section('title', 'Изменить перевозчика')

@section('content')
    @include('admin.components.page-header', [
        'title' => 'Изменить перевозчика',
        'subtitle' => $provider['name'],
    ])

    @component('admin.components.card')
        <form method="POST" action="{{ route('admin.providers.update', $provider['id']) }}" class="space-y-6 p-6">
            @csrf
            @method('PUT')

            @include('admin.components.input', [
                'name' => 'name',
                'label' => 'Название',
                'required' => true,
                'value' => $provider['name'],
                'autofocus' => true,
            ])

            @include('admin.components.select', [
                'name' => 'user_id',
                'label' => 'Пользователь',
                'placeholder' => 'Без пользователя',
                'options' => $users->pluck('name', 'id')->toArray(),
                'value' => $provider['user_id'],
            ])

            <div class="flex items-center gap-3 pt-2">
                @include('admin.components.button', ['slot' => 'Сохранить', 'type' => 'submit'])
                @include('admin.components.button', [
                    'href' => route('admin.providers.index'),
                    'variant' => 'secondary',
                    'slot' => 'Отмена',
                ])
            </div>
        </form>
    @endcomponent
@endsection
