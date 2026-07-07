@extends('admin.layouts.app')

@section('title', 'Таможни')

@section('content')
    @component('admin.components.page-header', [
        'title' => 'Таможни',
        'subtitle' => 'Управление таможенными организациями',
    ])
        @slot('actions')
            @include('admin.components.button', [
                'href' => route('admin.customers.create'),
                'slot' => 'Добавить таможню',
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
                        <th class="px-4 py-3">Телефон</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Документ</th>
                        <th class="px-4 py-3 text-right">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($customers as $customer)
                        <tr class="{{ $customer->trashed() ? 'bg-slate-50 opacity-75' : '' }}">
                            <td class="px-4 py-3">{{ $customer->id }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $customer->name }}</td>
                            <td class="px-4 py-3">{{ $customer->phone ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $customer->email ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $customer->document_number ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                @if ($customer->trashed())
                                    <form method="POST" action="{{ route('admin.customers.restore', $customer->id) }}" class="inline-block">
                                        @csrf
                                        <button type="submit" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">Восстановить</button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.customers.edit', $customer->id) }}" class="mr-3 text-sm font-medium text-blue-600 hover:text-blue-700">Изменить</a>
                                    @include('admin.components.delete-form', [
                                        'action' => route('admin.customers.destroy', $customer->id),
                                        'confirm' => 'Удалить таможню?',
                                        'slot' => 'Удалить',
                                    ])
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                @include('admin.components.empty-state', ['message' => 'Таможни не найдены'])
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('admin.components.pagination', ['paginator' => $customers])
    @endcomponent
@endsection
