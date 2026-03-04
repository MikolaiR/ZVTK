@extends('client.layouts.app')

@section('content')
    @php
        $currentParkingId = ($auto['current_location']['type_label'] ?? null) === 'Стоянка'
            ? ($auto['current_location']['location_id'] ?? null)
            : null;

        $availableParkings = collect($parkings)
            ->filter(fn ($parking) => ! $currentParkingId || (int) $parking['id'] !== (int) $currentParkingId)
            ->values();

        $statusClasses = [1 => 'bg-sky-700', 3 => 'bg-sky-700'];
        $transitionErrorFields = ['action', 'customer_id', 'arrival_date', 'parking_id', 'sold_at', 'note', 'photos.0', 'videos.0', 'documents.0'];
        $hasTransitionErrors = collect($transitionErrorFields)->contains(fn ($field) => $errors->has($field));
    @endphp

    <section
        x-data="autoShowPage(@js([
            'autoId' => $auto['id'],
            'slides' => collect($auto['media']['photos'])->map(fn ($p) => ['kind' => 'photo', 'url' => $p['url'], 'thumb' => $p['thumb_url'] ?: $p['url']])->values()
                ->concat(collect($auto['media']['videos'])->map(fn ($v) => ['kind' => 'video', 'url' => $v['url'], 'thumb' => null]))
                ->values(),
            'actions' => $actions,
            'customers' => $customers,
            'parkings' => $parkings,
            'availableParkings' => $availableParkings,
            'oldAction' => old('action', ''),
            'hasTransitionErrors' => $hasTransitionErrors,
            'oldCustomerId' => old('customer_id', ''),
            'oldParkingId' => old('parking_id', ''),
            'oldArrivalDate' => old('arrival_date', now()->format('Y-m-d')),
            'oldSoldAt' => old('sold_at', now()->format('Y-m-d')),
            'oldNote' => old('note', ''),
        ]))"
        x-init="init()"
        class="space-y-5"
    >
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold tracking-tight">{{ $auto['title'] ?: 'Автомобиль' }}</h1>
            <a href="{{ route('autos.index') }}" class="client-btn client-btn-outline">К списку</a>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            <aside class="order-1 space-y-3 md:order-2">
                <div class="client-card p-4">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Статус</h2>
                    <span class="mt-3 inline-flex rounded px-2 py-0.5 text-xs text-white {{ $statusClasses[(int) $auto['status']] ?? 'bg-slate-800' }}">
                        {{ $auto['status_label'] }}
                    </span>
                </div>

                <div class="client-card p-4">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Действия</h2>
                    <div class="mt-3 space-y-2">
                        <template x-for="action in actions" :key="action.key">
                            <button
                                type="button"
                                class="client-btn w-full"
                                :class="buttonClass(action.variant)"
                                @click="handleAction(action.key)"
                                x-text="action.label"
                            ></button>
                        </template>
                    </div>
                </div>
            </aside>

            <section class="order-2 space-y-4 md:order-1 md:col-span-2">
                <div class="client-card p-4">
                    <h2 class="text-base font-semibold">Медиа</h2>

                    <template x-if="slides.length">
                        <div>
                            <p class="mb-2 mt-1 text-sm text-slate-500"><span x-text="slideIndex + 1"></span> / <span x-text="slides.length"></span></p>
                            <div class="relative overflow-hidden rounded-xl border border-slate-200 bg-black">
                                <template x-if="currentSlide()?.kind === 'photo'">
                                    <img :src="currentSlide()?.url" alt="media" class="aspect-video w-full object-contain">
                                </template>
                                <template x-if="currentSlide()?.kind === 'video'">
                                    <video :src="currentSlide()?.url" controls class="aspect-video w-full"></video>
                                </template>

                                <button type="button" class="absolute left-2 top-1/2 rounded-full bg-white/90 px-2 py-1" @click="prevSlide">‹</button>
                                <button type="button" class="absolute right-2 top-1/2 rounded-full bg-white/90 px-2 py-1" @click="nextSlide">›</button>
                            </div>

                            <div class="mt-3 grid grid-cols-5 gap-2 md:grid-cols-8">
                                <template x-for="(slide, idx) in slides" :key="idx">
                                    <button type="button" class="h-14 overflow-hidden rounded border" :class="idx === slideIndex ? 'ring-2 ring-sky-700' : ''" @click="slideIndex = idx">
                                        <template x-if="slide.kind === 'photo'">
                                            <img :src="slide.thumb || slide.url" class="h-full w-full object-cover" alt="thumb">
                                        </template>
                                        <template x-if="slide.kind === 'video'">
                                            <span class="flex h-full w-full items-center justify-center bg-black text-[10px] text-white">Видео</span>
                                        </template>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>

                    <template x-if="!slides.length">
                        <div class="mt-3 flex aspect-video items-center justify-center rounded-xl border border-slate-200 bg-slate-100 text-slate-500">Нет медиа</div>
                    </template>
                </div>

                <div class="client-card p-4">
                    <h2 class="text-base font-semibold">Документы</h2>
                    @if (!empty($auto['media']['documents']))
                        <ul class="mt-3 space-y-2">
                            @foreach ($auto['media']['documents'] as $document)
                                <li class="flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <span class="mr-3 truncate">{{ $document['file_name'] ?: $document['name'] }}</span>
                                    <a href="{{ $document['url'] }}" target="_blank" class="client-btn client-btn-outline px-2! py-1! text-xs!">Скачать</a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-2 text-sm text-slate-500">Нет документов</p>
                    @endif
                </div>

                <div class="client-card p-4">
                    <h2 class="text-base font-semibold">Информация</h2>
                    <dl class="mt-3 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                        <div><dt class="text-slate-500">VIN</dt><dd class="font-medium">{{ $auto['vin'] ?: '—' }}</dd></div>
                        <div><dt class="text-slate-500">Бренд / Модель</dt><dd class="font-medium">{{ trim(($auto['brand'] ?? '') . ' ' . ($auto['model'] ?? '')) ?: '—' }}</dd></div>
                        <div><dt class="text-slate-500">Цвет</dt><dd class="font-medium">{{ $auto['color'] ?: '—' }}</dd></div>
                        <div><dt class="text-slate-500">Год</dt><dd class="font-medium">{{ $auto['year'] ?: '—' }}</dd></div>
                        <div><dt class="text-slate-500">Цена</dt><dd class="font-medium">{{ $auto['price'] ? number_format((int) $auto['price'], 0, '.', ' ') . ' ₽' : '—' }}</dd></div>
                        <div><dt class="text-slate-500">Дата отправки</dt><dd class="font-medium">{{ $auto['departure_date'] ?: '—' }}</dd></div>
                        <div><dt class="text-slate-500">Компания</dt><dd class="font-medium">{{ $auto['company']['name'] ?? '—' }}</dd></div>
                        <div><dt class="text-slate-500">Отправитель</dt><dd class="font-medium">{{ $auto['sender']['name'] ?? '—' }}</dd></div>
                        <div><dt class="text-slate-500">Перевозчик</dt><dd class="font-medium">{{ $auto['provider']['name'] ?? '—' }}</dd></div>
                    </dl>
                </div>

                <div class="client-card p-4">
                    <h2 class="text-base font-semibold">Текущая локация</h2>
                    @if (!empty($auto['current_location']))
                        <dl class="mt-3 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                            <div><dt class="text-slate-500">Тип</dt><dd class="font-medium">{{ $auto['current_location']['type_label'] }}</dd></div>
                            <div><dt class="text-slate-500">Название</dt><dd class="font-medium">{{ $auto['current_location']['name'] ?: '—' }}</dd></div>
                            <div><dt class="text-slate-500">Статус</dt><dd class="font-medium">{{ $auto['current_location']['status_label'] ?: '—' }}</dd></div>
                            <div><dt class="text-slate-500">Период</dt><dd class="font-medium">{{ $auto['current_location']['started_at'] ?: '—' }} — {{ $auto['current_location']['ended_at'] ?: 'н.в.' }}</dd></div>
                            <div class="sm:col-span-2"><dt class="text-slate-500">Принял</dt><dd class="font-medium">{{ $auto['current_location']['accepted_by']['name'] ?? '—' }}</dd></div>
                            @if (!empty($auto['current_location']['acceptance_note']))
                                <div class="sm:col-span-2"><dt class="text-slate-500">Примечание</dt><dd class="font-medium">{{ $auto['current_location']['acceptance_note'] }}</dd></div>
                            @endif
                        </dl>
                    @else
                        <p class="mt-2 text-sm text-slate-500">Нет активной локации</p>
                    @endif
                </div>

                <div class="client-card p-4">
                    <h2 class="text-base font-semibold">История перемещений</h2>
                    @if (!empty($auto['periods']))
                        <div class="mt-3 divide-y divide-slate-200">
                            @foreach ($auto['periods'] as $period)
                                <div class="grid grid-cols-1 gap-2 py-3 text-sm sm:grid-cols-5">
                                    <div><span class="text-slate-500">Тип</span><div class="font-medium">{{ $period['type_label'] }}</div></div>
                                    <div><span class="text-slate-500">Название</span><div class="font-medium">{{ $period['name'] ?: '—' }}</div></div>
                                    <div><span class="text-slate-500">Статус</span><div class="font-medium">{{ $period['status_label'] }}</div></div>
                                    <div><span class="text-slate-500">Период</span><div class="font-medium">{{ $period['started_at'] ?: '—' }} — {{ $period['ended_at'] ?: 'н.в.' }}</div></div>
                                    <div><span class="text-slate-500">Принял</span><div class="font-medium">{{ $period['accepted_by']['name'] ?? '—' }}</div></div>
                                    @if (!empty($period['acceptance_note']))
                                        <div class="sm:col-span-5"><span class="text-slate-500">Примечание</span><div class="font-medium">{{ $period['acceptance_note'] }}</div></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-2 text-sm text-slate-500">История отсутствует</p>
                    @endif
                </div>
            </section>
        </div>

        <div x-cloak x-show="transitionOpen" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50 p-4" @click.self="closeTransition">
            <div class="client-card w-full max-w-2xl p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold" x-text="transitionTitle()"></h3>
                    <button type="button" class="rounded p-1 text-slate-500 hover:bg-slate-100" @click="closeTransition">✕</button>
                </div>

                <form :action="`/autos/${autoId}/transitions`" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" :value="activeAction">

                    <template x-if="requiresCustomer()">
                        <div>
                            <label class="mb-1 block text-sm text-slate-600">Таможня</label>
                            <select name="customer_id" x-model="form.customer_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Выберите таможню</option>
                                <template x-for="customer in customers" :key="customer.id">
                                    <option :value="String(customer.id)" x-text="customer.name"></option>
                                </template>
                            </select>
                            @error('customer_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </template>

                    <template x-if="requiresArrivalDate()">
                        <div>
                            <label class="mb-1 block text-sm text-slate-600">Дата прибытия</label>
                            <input type="date" name="arrival_date" x-model="form.arrival_date" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            @error('arrival_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </template>

                    <template x-if="requiresParking()">
                        <div>
                            <label class="mb-1 block text-sm text-slate-600">Стоянка</label>
                            <select name="parking_id" x-model="form.parking_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Выберите стоянку</option>
                                <template x-for="parking in parkingOptions()" :key="parking.id">
                                    <option :value="String(parking.id)" x-text="parking.name"></option>
                                </template>
                            </select>
                            @error('parking_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </template>

                    <template x-if="activeAction === 'sell'">
                        <div>
                            <label class="mb-1 block text-sm text-slate-600">Дата передачи</label>
                            <input type="date" name="sold_at" x-model="form.sold_at" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            @error('sold_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </template>

                    <div class="grid gap-3 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-sm text-slate-600">Фото</label>
                            <input type="file" name="photos[]" multiple accept="image/*" class="block w-full text-xs">
                            @error('photos.0') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm text-slate-600">Видео</label>
                            <input type="file" name="videos[]" multiple accept="video/*" class="block w-full text-xs">
                            @error('videos.0') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm text-slate-600">Документы</label>
                            <input type="file" name="documents[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx" class="block w-full text-xs">
                            @error('documents.0') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <template x-if="activeAction !== 'save_files'">
                        <div>
                            <label class="mb-1 block text-sm text-slate-600">Комментарий</label>
                            <textarea name="note" rows="3" x-model="form.note" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('note') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </template>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" class="client-btn client-btn-outline" @click="closeTransition">Отмена</button>
                        <button type="submit" class="client-btn client-btn-primary" :disabled="transitionDisabled()">Подтвердить</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-cloak x-show="storageOpen" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50 p-4" @click.self="storageOpen = false">
            <div class="client-card w-full max-w-3xl p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Стоимость хранения</h3>
                    <button type="button" class="rounded p-1 text-slate-500 hover:bg-slate-100" @click="storageOpen = false">✕</button>
                </div>

                <template x-if="storageLoading">
                    <p class="text-sm text-slate-500">Расчет стоимости...</p>
                </template>

                <template x-if="!storageLoading">
                    <div class="space-y-4">
                        <p class="text-sm text-slate-700">
                            Всего дней: <span class="font-semibold" x-text="storage.total_days"></span>,
                            Итого: <span class="font-semibold" x-text="formatPrice(storage.total_cost)"></span>
                        </p>

                        <template x-for="item in storage.per_parkings" :key="item.parking.id">
                            <div class="rounded-xl border border-slate-200">
                                <div class="border-b border-slate-200 px-3 py-2 text-sm font-medium">
                                    Стоянка: <span x-text="item.parking.name || '#' + item.parking.id"></span>
                                    — дней: <span x-text="item.total_days"></span>,
                                    сумма: <span x-text="formatPrice(item.total_cost)"></span>
                                </div>
                                <div class="max-h-56 overflow-auto">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Дата</th>
                                            <th class="px-3 py-2 text-left">Цена</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <template x-for="day in item.days" :key="day.date">
                                            <tr>
                                                <td class="px-3 py-1.5" x-text="day.date"></td>
                                                <td class="px-3 py-1.5" x-text="formatPrice(day.price)"></td>
                                            </tr>
                                        </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <div class="mt-4 flex justify-end">
                    <button type="button" class="client-btn client-btn-outline" @click="storageOpen = false">Закрыть</button>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        function autoShowPage(config) {
            return {
                autoId: config.autoId,
                slides: config.slides || [],
                slideIndex: 0,
                actions: config.actions || [],
                customers: config.customers || [],
                parkings: config.parkings || [],
                availableParkings: config.availableParkings || [],
                transitionOpen: false,
                storageOpen: false,
                storageLoading: false,
                activeAction: '',
                form: {
                    customer_id: config.oldCustomerId || '',
                    arrival_date: config.oldArrivalDate || '',
                    parking_id: config.oldParkingId || '',
                    sold_at: config.oldSoldAt || '',
                    note: config.oldNote || '',
                },
                storage: { total_days: 0, total_cost: 0, per_parkings: [] },

                init() {
                    if (config.hasTransitionErrors && config.oldAction) {
                        this.activeAction = config.oldAction;
                        this.transitionOpen = true;
                    }
                },

                currentSlide() {
                    return this.slides[this.slideIndex] || null;
                },

                nextSlide() {
                    if (!this.slides.length) return;
                    this.slideIndex = (this.slideIndex + 1) % this.slides.length;
                },

                prevSlide() {
                    if (!this.slides.length) return;
                    this.slideIndex = (this.slideIndex - 1 + this.slides.length) % this.slides.length;
                },

                buttonClass(variant) {
                    if (variant === 'outline') return 'client-btn-outline';
                    if (variant === 'danger') return 'bg-rose-700 text-white hover:opacity-90';
                    return 'client-btn-primary';
                },

                transitionTitle() {
                    const map = {
                        move_to_customs: 'Переместить на таможню',
                        move_to_parking: 'Переместить на стоянку',
                        accept_at_parking: 'Принять на стоянку',
                        move_to_customs_from_parking: 'Переместить на таможню',
                        move_to_other_parking: 'Переместить на другую стоянку',
                        sell: 'Передана владельцу',
                        save_files: 'Добавление файлов',
                    };
                    return map[this.activeAction] || 'Действие';
                },

                requiresCustomer() {
                    return this.activeAction === 'move_to_customs' || this.activeAction === 'move_to_customs_from_parking';
                },

                requiresArrivalDate() {
                    return this.requiresCustomer();
                },

                requiresParking() {
                    return this.activeAction === 'move_to_parking' || this.activeAction === 'move_to_other_parking';
                },

                parkingOptions() {
                    return this.activeAction === 'move_to_other_parking' ? this.availableParkings : this.parkings;
                },

                transitionDisabled() {
                    if (this.requiresCustomer()) return !this.form.customer_id;
                    if (this.requiresParking()) return !this.form.parking_id;
                    return false;
                },

                handleAction(key) {
                    if (key === 'storage_cost') {
                        this.openStorage();
                        return;
                    }

                    this.activeAction = key;
                    this.transitionOpen = true;
                },

                closeTransition() {
                    this.transitionOpen = false;
                    this.activeAction = '';
                },

                async openStorage() {
                    this.storageOpen = true;
                    this.storageLoading = true;
                    try {
                        const response = await fetch(`/autos/${this.autoId}/storage-cost`, { headers: { Accept: 'application/json' } });
                        this.storage = response.ok ? await response.json() : { total_days: 0, total_cost: 0, per_parkings: [] };
                    } catch (e) {
                        this.storage = { total_days: 0, total_cost: 0, per_parkings: [] };
                    } finally {
                        this.storageLoading = false;
                    }
                },

                formatPrice(value) {
                    return new Intl.NumberFormat('ru-RU').format(Number(value || 0));
                },
            };
        }
    </script>
@endpush
