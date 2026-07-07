@extends('admin.layouts.app')

@section('title', 'Автомобили')

@section('content')
    @component('admin.components.page-header', [
        'title' => 'Автомобили',
        'subtitle' => 'Управление автомобилями в системе',
    ])
        @slot('actions')
            @include('admin.components.button', [
                'href' => route('autos.create'),
                'slot' => 'Добавить автомобиль',
            ])
        @endslot
    @endcomponent

    @component('admin.components.card')
        <div class="p-4">
            <form method="GET" action="{{ route('admin.autos.index') }}" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                        placeholder="Поиск по VIN или названию"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600" />
                </div>
                <button type="submit"
                    class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">Поиск</button>
                @if ($filters['search'] ?? false)
                    <a href="{{ route('admin.autos.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Сбросить</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Превью</th>
                        <th class="px-4 py-3">Название</th>
                        <th class="px-4 py-3">VIN</th>
                        <th class="px-4 py-3">Статус</th>
                        <th class="px-4 py-3 text-right">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($autos as $auto)
                        <tr>
                            <td class="px-4 py-3">{{ $auto['id'] }}</td>
                            <td class="px-4 py-3">
                                @if ($auto['preview_url'])
                                    <img src="{{ $auto['preview_url'] }}" alt=""
                                        class="h-12 w-16 rounded object-cover" />
                                @else
                                    <span class="text-xs text-slate-400">Нет фото</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $auto['title'] ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $auto['vin'] ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-700">
                                    {{ $auto['status_label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.autos.edit', $auto['id']) }}"
                                    class="mr-3 text-sm font-medium text-blue-600 hover:text-blue-700">Изменить</a>
                                @include('admin.components.delete-form', [
                                    'action' => route('admin.autos.destroy', $auto['id']),
                                    'confirm' => 'Удалить автомобиль?',
                                    'slot' => 'Удалить',
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                @include('admin.components.empty-state', [
                                    'message' => 'Автомобили не найдены',
                                ])
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('admin.components.pagination', ['paginator' => $autos])
    @endcomponent
@endsection
