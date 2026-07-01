@extends('client.layouts.app')

@section('content')
    @php
        $statusClasses = [1 => 'bg-sky-700', 3 => 'bg-sky-700'];
    @endphp

    <section class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Список автомобилей</h1>
                <p class="mt-1 text-sm text-slate-500">Краткий список с актуальным статусом, локацией и переходом в карточку.</p>
            </div>
            @can('create_auto')
                <a href="{{ route('autos.create') }}" class="client-btn client-btn-primary">Добавить автомобиль</a>
            @endcan
        </div>

        <form method="GET" action="{{ route('autos.index') }}" class="client-card grid gap-3 p-4 sm:grid-cols-4" x-data="{ status: '{{ (string) ($filters['status'] ?? '') }}' }">
            <input type="hidden" name="direction" value="{{ $sort['direction'] ?? 'asc' }}" />
            <div>
                <label for="vin" class="mb-1 block text-sm text-slate-600">VIN</label>
                <input id="vin" name="vin" value="{{ $filters['vin'] ?? '' }}" x-on:input.debounce.500ms="$el.form.submit()" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div>
                <label for="status" class="mb-1 block text-sm text-slate-600">Статус</label>
                <select id="status" name="status" x-model="status" x-on:change="if ($event.target.value !== '4') { document.getElementById('parking_id').value = ''; } $el.form.submit()" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Все</option>
                    <option value="1" @selected(($filters['status'] ?? null) == '1')>Доставка</option>
                    <option value="2" @selected(($filters['status'] ?? null) == '2')>Таможня</option>
                    <option value="3" @selected(($filters['status'] ?? null) == '3')>Доставка на стоянку</option>
                    <option value="4" @selected(($filters['status'] ?? null) == '4')>Стоянка</option>
                    <option value="5" @selected(($filters['status'] ?? null) == '5')>Передана владельцу</option>
                </select>
            </div>
            <div x-cloak x-show="status === '4'">
                <label for="parking_id" class="mb-1 block text-sm text-slate-600">Стоянка</label>
                <select id="parking_id" name="parking_id" x-on:change="$el.form.submit()" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Все стоянки</option>
                    @foreach ($parkings as $parking)
                        <option value="{{ $parking->id }}" @selected(($filters['parking_id'] ?? null) == (string) $parking->id)>{{ $parking->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="client-btn client-btn-primary">Применить</button>
                <a href="{{ route('autos.index') }}" class="client-btn client-btn-outline">Сброс</a>
                @if (($filters['status'] ?? null) === '4')
                    <a href="{{ route('autos.export', request()->query()) }}" class="client-btn client-btn-outline">Выгрузить в Excel</a>
                @endif
            </div>
        </form>

        <div class="client-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium uppercase tracking-wider">Фото</th>
                        <th class="px-4 py-3 text-left font-medium uppercase tracking-wider">Автомобиль</th>
                        <th class="px-4 py-3 text-left font-medium uppercase tracking-wider">
                            <a href="{{ route('autos.index', array_merge(request()->query(), ['direction' => ($sort['direction'] ?? 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="inline-flex items-center gap-1 hover:text-slate-700">
                                <span>Дата отправки</span>
                                <span class="text-xs">{{ ($sort['direction'] ?? 'asc') === 'asc' ? '↑' : '↓' }}</span>
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left font-medium uppercase tracking-wider">Статус</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                    @forelse ($autos as $auto)
                        <tr data-parking-highlight="{{ $auto['parking_highlight'] ?? 'none' }}" class="{{ ($auto['parking_highlight'] ?? null) === 'warning' ? 'bg-amber-50 hover:bg-amber-100/80' : (($auto['parking_highlight'] ?? null) === 'danger' ? 'bg-red-100 hover:bg-red-200/80' : 'hover:bg-slate-50/80') }}">
                            <td class="px-4 py-3">
                                <div class="h-12 w-20 overflow-hidden rounded border border-slate-200">
                                    @if ($auto['preview_url'])
                                        <img src="{{ $auto['preview_url'] }}" alt="{{ $auto['title'] }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-slate-100 text-[10px] text-slate-500">Нет фото</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('autos.show', $auto['id']) }}" class="font-medium text-slate-800 hover:text-(--client-primary) hover:underline">
                                    {{ $auto['title'] }}
                                </a>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $auto['year'] ?? '—' }} • {{ $auto['color_name'] ?? 'Цвет не указан' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $auto['departure_date'] ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded px-2 py-0.5 text-xs text-white {{ $statusClasses[(int) $auto['status']] ?? 'bg-slate-800' }}">
                                    {{ $auto['status_label'] }}
                                </span>
                                @if (($auto['status_detailed_label'] ?? null) && $auto['status_detailed_label'] !== $auto['status_label'])
                                    <div class="mt-1 text-xs text-slate-600">{{ $auto['status_detailed_label'] }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">Нет результатов</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if ($autos->hasPages())
                <div class="border-t border-slate-200 px-4 py-3">
                    {{ $autos->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
