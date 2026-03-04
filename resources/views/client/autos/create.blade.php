@extends('client.layouts.app')

@section('content')
    <section class="max-w-5xl space-y-4"
             x-data="createAutoForm({
                modelsEndpoint: '/api/brands/:id/models',
                initialBrand: '{{ old('auto_brand_id') }}',
                initialModel: '{{ old('auto_model_id') }}',
             })"
             x-init="init()"
    >
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold tracking-tight">Добавить автомобиль</h1>
            <a href="{{ route('autos.index') }}" class="client-btn client-btn-outline">К списку</a>
        </div>

        <form
            action="{{ route('autos.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="client-card space-y-6 p-5"
            @submit="submitWithProgress"
        >
            @csrf

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Бренд</label>
                    <select name="auto_brand_id" x-model="brandId" @change="onBrandChange" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Выберите бренд</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" @selected(old('auto_brand_id') == $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                    @error('auto_brand_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Модель</label>
                    <select name="auto_model_id" x-model="modelId" :disabled="loadingModels || models.length === 0" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm disabled:opacity-60">
                        <option value="">Выберите модель</option>
                        <template x-for="model in models" :key="model.id">
                            <option :value="String(model.id)" x-text="model.name"></option>
                        </template>
                    </select>
                    @error('auto_model_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Цвет</label>
                    <select name="color_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Не выбран</option>
                        @foreach ($colors as $color)
                            <option value="{{ $color->id }}" @selected(old('color_id') == $color->id)>
                                {{ $color->name }} {{ $color->name_ru }}
                            </option>
                        @endforeach
                    </select>
                    @error('color_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">VIN</label>
                    <input name="vin" value="{{ old('vin') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    @error('vin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Дата отправки</label>
                    <input type="date" name="departure_date" value="{{ old('departure_date') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    @error('departure_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Год</label>
                    <input type="number" name="year" min="1900" max="{{ date('Y') }}" value="{{ old('year') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    @error('year') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Цена</label>
                    <input type="number" name="price" min="0" value="{{ old('price') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2 text-sm text-slate-500">
                    Заголовок формируется автоматически: бренд + модель + VIN.
                </div>
            </div>

            <div class="space-y-4">
                <h2 class="text-base font-semibold">Файлы</h2>
                @include('client.components.file-upload-box')
            </div>

            <div>
                <button
                    type="submit"
                    class="client-btn client-btn-primary"
                    :disabled="uploading"
                    :class="uploading ? 'opacity-60 cursor-not-allowed' : ''"
                >
                    <span x-show="!uploading">Добавить</span>
                    <span x-show="uploading">Загрузка...</span>
                </button>
            </div>
        </form>
    </section>
@endsection

@push('scripts')
    <script>
        function createAutoForm(config) {
            return {
                brandId: config.initialBrand || '',
                modelId: config.initialModel || '',
                models: [],
                loadingModels: false,
                ...window.createUnifiedUploadState(),

                async init() {
                    if (this.brandId) {
                        await this.loadModels(this.brandId);
                        if (this.modelId) {
                            this.modelId = String(this.modelId);
                        }
                    }
                },

                async onBrandChange() {
                    this.modelId = '';
                    if (!this.brandId) {
                        this.models = [];
                        return;
                    }
                    await this.loadModels(this.brandId);
                },

                async loadModels(brandId) {
                    this.loadingModels = true;
                    try {
                        const url = config.modelsEndpoint.replace(':id', brandId);
                        const response = await fetch(url, { headers: { Accept: 'application/json' } });
                        this.models = response.ok ? await response.json() : [];
                    } catch (e) {
                        this.models = [];
                    } finally {
                        this.loadingModels = false;
                    }
                },
            };
        }
    </script>
@endpush
