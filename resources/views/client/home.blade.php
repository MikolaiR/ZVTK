@extends('client.layouts.app')

@section('content')
    <section class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Статусы автомобилей</h1>
                <p class="mt-1 text-sm text-slate-500">Быстрый переход к спискам по текущему статусу.</p>
            </div>
            @can('create_auto')
                <a href="{{ route('autos.create') }}" class="client-btn client-btn-primary">Добавить автомобиль</a>
            @endcan
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <a href="{{ route('autos.index') }}" class="client-card group relative overflow-hidden p-5">
                <div class="absolute inset-0 bg-[url('/images/not_photo.png')] bg-cover bg-center opacity-30 transition group-hover:scale-[1.03]"></div>
                <div class="absolute inset-0 bg-linear-to-t from-slate-900/60 to-transparent"></div>
                <div class="relative text-white">
                    <p class="text-xs uppercase tracking-[0.2em] opacity-80">Список</p>
                    <h2 class="mt-2 text-lg font-semibold">Все автомобили</h2>
                </div>
            </a>

            @foreach ($statuses as $status)
                <a href="{{ route('autos.index', ['status' => $status['value']]) }}" class="client-card group relative overflow-hidden p-5">
                    <div class="absolute inset-0 opacity-70 transition group-hover:scale-[1.03]" style="background-image: url('{{ $status['background'] }}'); background-size: cover; background-position: center;"></div>
                    <div class="absolute inset-0 bg-linear-to-t from-slate-900/65 to-transparent"></div>
                    <div class="relative text-white">
                        <p class="text-xs uppercase tracking-[0.2em] opacity-80">Список</p>
                        <h2 class="mt-2 text-lg font-semibold">{{ $status['label'] }}</h2>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endsection
