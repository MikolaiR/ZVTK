@extends('admin.layouts.app')

@section('title', 'Стоянки')

@section('content')
    @component('admin.components.page-header', [
        'title' => 'Стоянки',
        'subtitle' => 'Управление стоянками и компаниями-владельцами',
    ])
        @slot('actions')
            @include('admin.components.button', [
                'href' => route('admin.parkings.create'),
                'slot' => 'Добавить стоянку',
            ])
        @endslot
    @endcomponent

    @component('admin.components.card')
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Название</th>
                        <th class="px-4 py-3">Адрес</th>
                        <th class="px-4 py-3">Компания</th>
                        <th class="px-4 py-3 text-right">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($parkings as $parking)
                        <tr>
                            <td class="px-4 py-3">{{ $parking['id'] }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $parking['name'] ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $parking['address'] }}</td>
                            <td class="px-4 py-3">{{ $parking['company']['name'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.parkings.edit', $parking['id']) }}" class="mr-3 text-sm font-medium text-blue-600 hover:text-blue-700">Изменить</a>
                                @include('admin.components.delete-form', [
                                    'action' => route('admin.parkings.destroy', $parking['id']),
                                    'confirm' => 'Удалить стоянку?',
                                    'slot' => 'Удалить',
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                @include('admin.components.empty-state', ['message' => 'Стоянки не найдены'])
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('admin.components.pagination', ['paginator' => $parkings])
    @endcomponent
@endsection
