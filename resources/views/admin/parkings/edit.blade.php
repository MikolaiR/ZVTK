@extends('admin.layouts.app')

@section('title', 'Изменить стоянку')

@section('content')
    @include('admin.components.page-header', [
        'title' => 'Изменить стоянку',
        'subtitle' => $parking['name'] ?? $parking['address'],
    ])

    @component('admin.components.card')
        <form method="POST" action="{{ route('admin.parkings.update', $parking['id']) }}" class="space-y-6 p-6">
            @csrf
            @method('PUT')

            @include('admin.components.input', [
                'name' => 'name',
                'label' => 'Название',
                'value' => $parking['name'] ?? '',
            ])

            @include('admin.components.input', [
                'name' => 'address',
                'label' => 'Адрес',
                'required' => true,
                'value' => $parking['address'] ?? '',
                'autofocus' => true,
            ])

            @include('admin.components.select', [
                'name' => 'company_id',
                'label' => 'Компания',
                'placeholder' => 'Без компании',
                'options' => $companies->pluck('name', 'id')->toArray(),
                'value' => $parking['company_id'] ?? '',
            ])

            <div class="flex items-center gap-3 pt-2">
                @include('admin.components.button', ['slot' => 'Сохранить', 'type' => 'submit'])
                @include('admin.components.button', [
                    'href' => route('admin.parkings.index'),
                    'variant' => 'secondary',
                    'slot' => 'Отмена',
                ])
            </div>
        </form>
    @endcomponent

    <div class="mt-8">
        @component('admin.components.page-header', [
            'title' => 'Тарифы',
            'subtitle' => 'Цены и периоды действия',
        ])
        @endcomponent

        @component('admin.components.card')
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Название</th>
                            <th class="px-4 py-3">Цена</th>
                            <th class="px-4 py-3">Начало</th>
                            <th class="px-4 py-3">Окончание</th>
                            <th class="px-4 py-3 text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($parking['prices'] as $price)
                            <tr>
                                <form method="POST"
                                    action="{{ route('admin.parkings.prices.update', [$parking['id'], $price['id']]) }}">
                                    @csrf
                                    @method('PUT')
                                    <td class="px-4 py-3">
                                        <input type="text" name="name" value="{{ $price['name'] }}" required
                                            class="w-full rounded border border-slate-300 px-2 py-1 text-sm focus:border-blue-600 focus:outline-none" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="price" value="{{ $price['price'] }}" min="0"
                                            class="w-24 rounded border border-slate-300 px-2 py-1 text-sm focus:border-blue-600 focus:outline-none" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="date" name="date_start" value="{{ $price['date_start'] }}" required
                                            class="rounded border border-slate-300 px-2 py-1 text-sm focus:border-blue-600 focus:outline-none" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="date" name="date_end" value="{{ $price['date_end'] }}"
                                            class="rounded border border-slate-300 px-2 py-1 text-sm focus:border-blue-600 focus:outline-none" />
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button type="submit"
                                            class="mr-2 text-sm font-medium text-blue-600 hover:text-blue-700">Сохранить</button>
                                </form>
                                @include('admin.components.delete-form', [
                                    'action' => route('admin.parkings.prices.destroy', [
                                        $parking['id'],
                                        $price['id'],
                                    ]),
                                    'confirm' => 'Удалить тариф?',
                                    'slot' => 'Удалить',
                                ])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                    @include('admin.components.empty-state', [
                                        'message' => 'Тарифы не добавлены',
                                    ])
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 p-6">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">Добавить тариф</h3>
                <form method="POST" action="{{ route('admin.parkings.prices.store', $parking['id']) }}"
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    @csrf

                    @include('admin.components.input', [
                        'name' => 'name',
                        'label' => 'Название',
                        'required' => true,
                        'wrapperClass' => 'lg:col-span-2',
                    ])
                    @include('admin.components.input', [
                        'name' => 'price',
                        'label' => 'Цена',
                        'type' => 'number',
                        'min' => '0',
                    ])
                    @include('admin.components.input', [
                        'name' => 'date_start',
                        'label' => 'Начало',
                        'type' => 'date',
                        'required' => true,
                    ])
                    @include('admin.components.input', [
                        'name' => 'date_end',
                        'label' => 'Окончание',
                        'type' => 'date',
                    ])

                    <div class="flex items-end">
                        @include('admin.components.button', ['slot' => 'Добавить', 'type' => 'submit'])
                    </div>
                </form>
            </div>
        @endcomponent
    </div>
@endsection
