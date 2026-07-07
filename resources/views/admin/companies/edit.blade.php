@extends('admin.layouts.app')

@section('title', 'Изменить компанию')

@section('content')
    @include('admin.components.page-header', [
        'title' => 'Изменить компанию',
        'subtitle' => $company['name'],
    ])

    @component('admin.components.card')
        <form method="POST" action="{{ route('admin.companies.update', $company['id']) }}" class="space-y-6 p-6">
            @csrf
            @method('PUT')

            @include('admin.components.input', [
                'name' => 'name',
                'label' => 'Название',
                'required' => true,
                'value' => $company['name'],
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
