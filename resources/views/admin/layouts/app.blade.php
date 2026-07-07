<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Админка') — {{ config('app.name', 'ZVTK') }}</title>

    @vite(['resources/css/app.css', 'resources/js/admin.js'])

    <style>
        html,
        body {
            margin: 0;
            padding: 0;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('head')
</head>

<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen">
        <div x-cloak x-show="sidebarOpen" @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"></div>

        <aside
            class="fixed inset-y-0 left-0 z-50 w-64 transform border-r border-slate-200 bg-white transition-transform lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="flex h-16 items-center border-b border-slate-200 px-6">
                <a href="{{ route('admin.home') }}" class="text-lg font-bold text-blue-700">
                    {{ config('app.name', 'ZVTK') }}
                </a>
            </div>

            <nav class="space-y-1 p-4">
                @php
                    $items = [
                        ['admin.home', 'Главная'],
                        ['admin.users.index', 'Пользователи'],
                        ['admin.roles.index', 'Роли'],
                        ['admin.permissions.index', 'Разрешения'],
                        ['admin.autos.index', 'Автомобили'],
                        ['admin.auto.brands.index', 'Марки'],
                        ['admin.auto.models.index', 'Модели'],
                        ['admin.colors.index', 'Цвета'],
                        ['admin.companies.index', 'Компании'],
                        ['admin.providers.index', 'Перевозчики'],
                        ['admin.parkings.index', 'Стоянки'],
                        ['admin.customers.index', 'Таможни'],
                    ];
                @endphp

                @foreach ($items as [$route, $label])
                    @include('admin.components.nav-link', ['route' => $route, 'label' => $label])
                @endforeach
            </nav>
        </aside>

        <div class="flex min-h-screen flex-col lg:ml-64">
            <header
                class="sticky top-0 z-30 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-2 lg:px-8">
                <button @click="sidebarOpen = !sidebarOpen" type="button"
                    class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="flex items-center gap-4">
                    <span class="text-sm text-slate-600">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700">
                            Выйти
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 px-4 pb-4 pt-2 lg:px-8 lg:pb-8">
                @include('admin.components.flash')

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
