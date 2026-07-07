@extends('admin.layouts.app')

@section('title', 'Изменить таможню')

@section('content')
    @include('admin.components.page-header', [
        'title' => 'Изменить таможню',
        'subtitle' => $customer['name'],
    ])

    @component('admin.components.card')
        <form method="POST" action="{{ route('admin.customers.update', $customer['id']) }}" class="space-y-6 p-6">
            @csrf
            @method('PUT')

            @include('admin.components.input', [
                'name' => 'name',
                'label' => 'Название',
                'required' => true,
                'value' => $customer['name'],
                'autofocus' => true,
            ])

            @include('admin.components.input', [
                'name' => 'phone',
                'label' => 'Телефон',
                'value' => $customer['phone'] ?? '',
            ])

            @include('admin.components.input', [
                'name' => 'email',
                'label' => 'Email',
                'type' => 'email',
                'value' => $customer['email'] ?? '',
            ])

            @include('admin.components.input', [
                'name' => 'document_number',
                'label' => 'Номер документа',
                'value' => $customer['document_number'] ?? '',
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
