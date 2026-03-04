@extends('client.layouts.app')

@section('content')
    <section class="mx-auto max-w-xl space-y-5">
        <header class="client-card p-5">
            <h1 class="text-2xl font-semibold tracking-tight">Профиль</h1>
            <p class="mt-2 text-sm text-slate-500">Данные текущего пользователя в клиентском кабинете.</p>
        </header>

        <article class="client-card p-5">
            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="text-xs uppercase tracking-wider text-slate-500">Имя</dt>
                    <dd class="mt-1 text-base text-slate-900">{{ $user['name'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-slate-500">Email</dt>
                    <dd class="mt-1 text-base text-slate-900">{{ $user['email'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-slate-500">Компания</dt>
                    <dd class="mt-1 text-base text-slate-900">{{ $company['name'] ?? '—' }}</dd>
                </div>
            </dl>

            <p class="mt-5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                Редактирование профиля доступно через админ-панель.
            </p>
        </article>
    </section>
@endsection
