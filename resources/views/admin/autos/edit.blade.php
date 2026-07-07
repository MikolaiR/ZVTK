@extends('admin.layouts.app')

@section('title', 'Изменить автомобиль')

@section('content')
    @include('admin.components.page-header', [
        'title' => 'Изменить автомобиль',
        'subtitle' => $auto['title'] ?? $auto['vin'],
    ])

    @php
        $modelList = $models
            ->map(fn($m) => ['id' => $m->id, 'brand_id' => $m->auto_brand_id, 'name' => $m->name])
            ->values();
        $brandId = old('auto_brand_id', $auto['auto_brand_id'] ?? '');
        $modelId = old('auto_model_id', $auto['auto_model_id'] ?? '');
    @endphp

    <div x-data="{
        activeTab: 'main',
        brandId: '{{ $brandId }}',
        modelId: '{{ $modelId }}',
        models: {{ $modelList->toJson() }},
        status: '{{ old('status', '') }}',
        locationId: '{{ old('location_id', '') }}',
        locationOptions: [],
        locationLists: {
            '2': {{ $customers->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values()->toJson() }},
            '3': {{ $parkings->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->values()->toJson() }},
            '4': {{ $parkings->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->values()->toJson() }},
        },
        filteredModels() {
            return this.models.filter(m => String(m.brand_id) === String(this.brandId));
        },
        updateLocationOptions() {
            this.locationOptions = this.locationLists[this.status] || [];
            if (!this.locationOptions.find(o => String(o.id) === String(this.locationId))) {
                this.locationId = '';
            }
        },
        init() {
            this.$watch('brandId', () => {
                if (!this.filteredModels().find(m => String(m.id) === String(this.modelId))) {
                    this.modelId = '';
                }
            });
            this.$watch('status', () => this.updateLocationOptions());
            this.updateLocationOptions();
        }
    }">
        <div class="mb-6 border-b border-slate-200">
            <nav class="-mb-px flex gap-6">
                <button type="button" @click="activeTab = 'main'"
                    :class="activeTab === 'main' ? 'border-blue-600 text-blue-600' :
                        'border-transparent text-slate-500 hover:text-slate-700'"
                    class="border-b-2 px-1 py-3 text-sm font-medium">
                    Основные данные
                </button>
                <button type="button" @click="activeTab = 'media'"
                    :class="activeTab === 'media' ? 'border-blue-600 text-blue-600' :
                        'border-transparent text-slate-500 hover:text-slate-700'"
                    class="border-b-2 px-1 py-3 text-sm font-medium">
                    Медиа и периоды
                </button>
            </nav>
        </div>

        <form method="POST" action="{{ route('admin.autos.update', $auto['id']) }}" enctype="multipart/form-data"
            class="space-y-6">
            @csrf
            @method('PUT')

            <div x-show="activeTab === 'main'" x-cloak>
                @component('admin.components.card')
                    <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">
                        @include('admin.components.input', [
                            'name' => 'vin',
                            'label' => 'VIN',
                            'required' => true,
                            'value' => $auto['vin'],
                        ])

                        @include('admin.components.input', [
                            'name' => 'title',
                            'label' => 'Название',
                            'value' => $auto['title'] ?? '',
                        ])

                        <div>
                            <label for="auto_brand_id" class="mb-1 block text-sm font-medium text-slate-700">Марка <span
                                    class="text-red-500">*</span></label>
                            <select id="auto_brand_id" x-model="brandId" name="auto_brand_id" required
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                                <option value="">Выберите марку</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                            @error('auto_brand_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="auto_model_id" class="mb-1 block text-sm font-medium text-slate-700">Модель <span
                                    class="text-red-500">*</span></label>
                            <select id="auto_model_id" x-model="modelId" name="auto_model_id" required
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                                <option value="">Выберите модель</option>
                                <template x-for="model_auto in filteredModels()" :key="model_auto.id">
                                    <option :value="model_auto.id" :selected="String(model_auto.id) === String(modelId)"
                                        x-text="model_auto.name"></option>
                                </template>
                            </select>
                            @error('auto_model_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @include('admin.components.select', [
                            'name' => 'color_id',
                            'label' => 'Цвет',
                            'placeholder' => 'Выберите цвет',
                            'options' => $colors->mapWithKeys(fn($c) => [$c->id => $c->name])->toArray(),
                            'value' => $auto['color_id'] ?? '',
                        ])

                        @include('admin.components.input', [
                            'name' => 'year',
                            'label' => 'Год',
                            'type' => 'number',
                            'value' => $auto['year'] ?? '',
                        ])

                        @include('admin.components.input', [
                            'name' => 'departure_date',
                            'label' => 'Дата отгрузки',
                            'type' => 'date',
                            'value' => $auto['departure_date'] ?? '',
                        ])

                        @include('admin.components.input', [
                            'name' => 'price',
                            'label' => 'Цена',
                            'type' => 'number',
                            'value' => $auto['price'] ?? '',
                        ])

                        @include('admin.components.select', [
                            'name' => 'status',
                            'label' => 'Статус',
                            'placeholder' => 'Выберите статус',
                            'options' => collect($statuses)->mapWithKeys(fn($s) => [$s['value'] => $s['label']])->toArray(),
                            'value' => $auto['status'] ?? '',
                        ])

                        @include('admin.components.select', [
                            'name' => 'company_id',
                            'label' => 'Компания',
                            'placeholder' => 'Без компании',
                            'options' => $companies->pluck('name', 'id')->toArray(),
                            'value' => $auto['company_id'] ?? '',
                        ])

                        @include('admin.components.select', [
                            'name' => 'sender_id',
                            'label' => 'Отправитель',
                            'placeholder' => 'Без отправителя',
                            'options' => $senders->pluck('name', 'id')->toArray(),
                            'value' => $auto['sender_id'] ?? '',
                        ])

                        @include('admin.components.select', [
                            'name' => 'provider_id',
                            'label' => 'Перевозчик',
                            'placeholder' => 'Без перевозчика',
                            'options' => $providers->pluck('name', 'id')->toArray(),
                            'value' => $auto['provider_id'] ?? '',
                        ])
                    </div>

                    <div class="flex items-center gap-3 border-t border-slate-200 px-6 py-4">
                        @include('admin.components.button', ['slot' => 'Сохранить', 'type' => 'submit'])
                        @include('admin.components.button', [
                            'href' => route('admin.autos.index'),
                            'variant' => 'secondary',
                            'slot' => 'Отмена',
                        ])
                    </div>
                @endcomponent
            </div>

            <div x-show="activeTab === 'media'" x-cloak>
                @component('admin.components.card')
                    <div class="p-6">
                        <h2 class="mb-4 text-lg font-semibold text-slate-900">Фотографии</h2>
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                            @forelse ($auto['media']['photos'] as $photo)
                                <div class="group relative rounded-lg border border-slate-200 bg-white p-2">
                                    <img src="{{ $photo['thumb_url'] }}" alt=""
                                        class="mb-2 h-24 w-full rounded object-cover" />
                                    <label class="flex items-center gap-2 text-xs text-slate-600">
                                        <input type="checkbox" name="remove_media[]" value="{{ $photo['id'] }}"
                                            class="rounded border-slate-300 text-red-600 focus:ring-red-600" />
                                        Удалить
                                    </label>
                                </div>
                            @empty
                                <p class="col-span-full text-sm text-slate-500">Фотографий нет.</p>
                            @endforelse
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-slate-700">Добавить фотографии</label>
                            <input type="file" name="photos[]" multiple accept="image/*"
                                class="mt-1 block w-full text-sm text-slate-600" />
                        </div>

                        <h2 class="mb-4 mt-8 text-lg font-semibold text-slate-900">Видео</h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            @forelse ($auto['media']['videos'] as $video)
                                <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3">
                                    <a href="{{ $video['url'] }}" target="_blank"
                                        class="text-sm text-blue-600 hover:underline">{{ $video['name'] }}</a>
                                    <label class="flex items-center gap-2 text-xs text-slate-600">
                                        <input type="checkbox" name="remove_media[]" value="{{ $video['id'] }}"
                                            class="rounded border-slate-300 text-red-600 focus:ring-red-600" />
                                        Удалить
                                    </label>
                                </div>
                            @empty
                                <p class="col-span-full text-sm text-slate-500">Видео нет.</p>
                            @endforelse
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-slate-700">Добавить видео</label>
                            <input type="file" name="videos[]" multiple accept="video/*"
                                class="mt-1 block w-full text-sm text-slate-600" />
                        </div>

                        <h2 class="mb-4 mt-8 text-lg font-semibold text-slate-900">Документы</h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            @forelse ($auto['media']['documents'] as $document)
                                <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3">
                                    <a href="{{ $document['url'] }}" target="_blank"
                                        class="text-sm text-blue-600 hover:underline">{{ $document['name'] }}</a>
                                    <label class="flex items-center gap-2 text-xs text-slate-600">
                                        <input type="checkbox" name="remove_media[]" value="{{ $document['id'] }}"
                                            class="rounded border-slate-300 text-red-600 focus:ring-red-600" />
                                        Удалить
                                    </label>
                                </div>
                            @empty
                                <p class="col-span-full text-sm text-slate-500">Документов нет.</p>
                            @endforelse
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-slate-700">Добавить документы</label>
                            <input type="file" name="documents[]" multiple
                                class="mt-1 block w-full text-sm text-slate-600" />
                        </div>
                    </div>

                    <div class="border-t border-slate-200 p-6">
                        <h2 class="mb-4 text-lg font-semibold text-slate-900">Периоды местоположения</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead
                                    class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                                    <tr>
                                        <th class="px-4 py-3">Статус</th>
                                        <th class="px-4 py-3">Тип</th>
                                        <th class="px-4 py-3">Локация</th>
                                        <th class="px-4 py-3">Начало</th>
                                        <th class="px-4 py-3">Окончание</th>
                                        <th class="px-4 py-3">Принял</th>
                                        <th class="px-4 py-3 text-right">Действия</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($auto['periods'] as $period)
                                        <tr>
                                            <td class="px-4 py-3">{{ $period['status_label'] }}</td>
                                            <td class="px-4 py-3">{{ $period['type_label'] }}</td>
                                            <td class="px-4 py-3">{{ $period['name'] ?? '—' }}</td>
                                            <td class="px-4 py-3">{{ $period['started_at'] ?? '—' }}</td>
                                            <td class="px-4 py-3">{{ $period['ended_at'] ?? '—' }}</td>
                                            <td class="px-4 py-3">{{ $period['accepted_by']['name'] ?? '—' }}</td>
                                            <td class="px-4 py-3 text-right">
                                                @if (is_null($period['ended_at']))
                                                    <form method="POST"
                                                        action="{{ route('admin.autos.periods.close', [$auto['id'], $period['id']]) }}"
                                                        class="inline-block">
                                                        @csrf
                                                        <button type="submit"
                                                            class="mr-2 text-sm font-medium text-amber-600 hover:text-amber-700">Закрыть</button>
                                                    </form>
                                                @endif
                                                @include('admin.components.delete-form', [
                                                    'action' => route('admin.autos.periods.destroy', [
                                                        $auto['id'],
                                                        $period['id'],
                                                    ]),
                                                    'confirm' => 'Удалить период?',
                                                    'slot' => 'Удалить',
                                                ])
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                                @include('admin.components.empty-state', [
                                                    'message' => 'Периоды не найдены',
                                                ])
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="flex items-center gap-3 border-t border-slate-200 px-6 py-4">
                            @include('admin.components.button', [
                                'slot' => 'Сохранить',
                                'type' => 'submit',
                            ])
                            @include('admin.components.button', [
                                'href' => route('admin.autos.index'),
                                'variant' => 'secondary',
                                'slot' => 'Отмена',
                            ])
                        </div>
                    @endcomponent
                </div>
        </form>

        @component('admin.components.card')
            <div class="p-6">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Добавить период</h2>
                <form method="POST" action="{{ route('admin.autos.periods.store', $auto['id']) }}"
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @csrf

                    <div>
                        <label for="period_status" class="mb-1 block text-sm font-medium text-slate-700">Статус <span
                                class="text-red-500">*</span></label>
                        <select id="period_status" x-model="status" name="status" required
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                            <option value="">Выберите статус</option>
                            @foreach ($statuses as $statusItem)
                                <option value="{{ $statusItem['value'] }}">{{ $statusItem['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="locationOptions.length > 0">
                        <label for="location_id" class="mb-1 block text-sm font-medium text-slate-700">Локация</label>
                        <select id="location_id" x-model="locationId" name="location_id"
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                            <option value="">Выберите локацию</option>
                            <template x-for="option in locationOptions" :key="option.id">
                                <option :value="option.id" x-text="option.name"></option>
                            </template>
                        </select>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-2">
                        <label for="note" class="mb-1 block text-sm font-medium text-slate-700">Примечание</label>
                        <input type="text" id="note" name="note"
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600" />
                    </div>

                    <div class="flex items-end">
                        @include('admin.components.button', ['slot' => 'Добавить', 'type' => 'submit'])
                    </div>
                </form>
            </div>
        @endcomponent
    </div>
@endsection
