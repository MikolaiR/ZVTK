@extends('admin.layouts.app')

@section('title', 'Главная')

@section('content')
    @include('admin.components.page-header', [
        'title' => 'Панель администратора',
        'subtitle' => 'Выберите раздел в боковом меню',
    ])

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @php
            $cards = [
                ['route' => 'admin.autos.index', 'label' => 'Автомобили', 'desc' => 'Управление автомобилями'],
                ['route' => 'admin.users.index', 'label' => 'Пользователи', 'desc' => 'Пользователи и роли'],
                ['route' => 'admin.parkings.index', 'label' => 'Стоянки', 'desc' => 'Стоянки и тарифы'],
                ['route' => 'admin.auto.brands.index', 'label' => 'Марки', 'desc' => 'Марки и модели'],
                ['route' => 'admin.companies.index', 'label' => 'Компании', 'desc' => 'Компании клиентов'],
                ['route' => 'admin.providers.index', 'label' => 'Перевозчики', 'desc' => 'Перевозчики'],
            ];
        @endphp

        @foreach ($cards as $card)
            <a href="{{ route($card['route']) }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-blue-300 hover:shadow-md">
                <h2 class="text-lg font-semibold text-slate-900">{{ $card['label'] }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $card['desc'] }}</p>
            </a>
        @endforeach
    </div>
@endsection
