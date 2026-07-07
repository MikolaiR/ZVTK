@extends('admin.layouts.app')

@section('title', 'Марки автомобилей')

@section('content')
    @component('admin.components.page-header', [
        'title' => 'Марки автомобилей',
        'subtitle' => 'Управление марками автомобилей',
    ])
        @slot('actions')
            @include('admin.components.button', [
                'href' => route('admin.auto.brands.create'),
                'slot' => 'Добавить марку',
            ])
        @endslot
    @endcomponent

    @component('admin.components.card')
        <div class="p-4">
            <form method="GET" action="{{ route('admin.auto.brands.index') }}" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Поиск по названию"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600"
                    />
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input
                        type="checkbox"
                        name="show_deleted"
                        value="1"
                        {{ ($filters['show_deleted'] ?? false) ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-600"
                    />
                    Показать удалённые
                </label>
                <button type="submit" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">
                    Фильтр
                </button>
                @if ($filters['search'] ?? $filters['show_deleted'] ?? false)
                    <a href="{{ route('admin.auto.brands.index') }}" class="text-sm text-slate-500 hover:text-slate-700">
                        Сбросить
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Название</th>
                        <th class="px-4 py-3">Моделей</th>
                        <th class="px-4 py-3">Создана</th>
                        <th class="px-4 py-3 text-right">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($brands as $brand)
                        <tr class="{{ $brand['deleted_at'] ? 'bg-slate-50 opacity-75' : '' }}">
                            <td class="px-4 py-3">{{ $brand['id'] }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $brand['name'] }}</td>
                            <td class="px-4 py-3">{{ $brand['models_count'] }}</td>
                            <td class="px-4 py-3">{{ $brand['created_at'] }}</td>
                            <td class="px-4 py-3 text-right">
                                @if ($brand['deleted_at'])
                                    <form method="POST" action="{{ route('admin.auto.brands.restore', $brand['id']) }}" class="inline-block">
                                        @csrf
                                        <button type="submit" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">
                                            Восстановить
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.auto.brands.edit', $brand['id']) }}" class="mr-3 text-sm font-medium text-blue-600 hover:text-blue-700">
                                        Изменить
                                    </a>
                                    @include('admin.components.delete-form', [
                                        'action' => route('admin.auto.brands.destroy', $brand['id']),
                                        'confirm' => 'Удалить марку?',
                                        'slot' => 'Удалить',
                                    ])
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                @include('admin.components.empty-state', ['message' => 'Марки не найдены'])
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('admin.components.pagination', ['paginator' => $brands])
    @endcomponent
@endsection
