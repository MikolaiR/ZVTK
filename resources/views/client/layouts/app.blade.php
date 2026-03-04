<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'AutoDom') }}</title>

    @vite(['resources/css/app.css', 'resources/js/client-blade.js'])

    <style>
        :root {
            --client-primary: #0f4c81;
            --client-accent: #ed7d31;
            --client-bg: #f4f7fb;
            --client-surface: #ffffff;
            --client-text: #0f172a;
            --client-muted: #64748b;
            --client-border: #d8e0eb;
        }

        .client-shell {
            background: radial-gradient(circle at top right, #e3eefb 0%, #f4f7fb 45%, #f8fafc 100%);
            color: var(--client-text);
        }

        .client-card {
            background: var(--client-surface);
            border: 1px solid var(--client-border);
            border-radius: 14px;
            box-shadow: 0 12px 30px -22px rgba(15, 76, 129, 0.45);
        }

        .client-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            border-radius: .65rem;
            padding: .55rem .9rem;
            font-size: .875rem;
            font-weight: 600;
            border: 1px solid transparent;
            transition: .2s ease;
        }

        .client-btn-primary {
            background: var(--client-primary);
            color: #fff;
        }

        .client-btn-primary:hover { opacity: .92; }

        .client-btn-outline {
            border-color: var(--client-primary);
            color: var(--client-primary);
            background: #fff;
        }

        .client-btn-outline:hover {
            background: var(--client-primary);
            color: #fff;
        }

        [x-cloak] { display: none !important; }
    </style>

    @stack('head')
</head>
<body class="client-shell min-h-screen antialiased">
<div
    x-data="{
        menuOpen: false,
        vinSearch: '{{ request('vin') }}',
        timer: null,
        toggleMenu() { this.menuOpen = !this.menuOpen },
        closeMenu() { this.menuOpen = false },
        onSearch() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => {
                const params = new URLSearchParams(window.location.search);
                if (this.vinSearch) {
                    params.set('vin', this.vinSearch);
                } else {
                    params.delete('vin');
                }

                if (window.location.pathname !== '/autos') {
                    window.location.href = '/autos' + (params.toString() ? `?${params.toString()}` : '');
                    return;
                }

                window.location.href = '/autos' + (params.toString() ? `?${params.toString()}` : '');
            }, 450)
        },
    }"
    @keydown.escape.window="closeMenu"
>
    <header class="sticky top-0 z-20 border-b border-(--client-border) bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3">
            <a href="/" class="text-lg font-bold tracking-tight text-(--client-primary)">AutoDom</a>

            <div class="flex-1">
                <label class="sr-only" for="client-vin-search">Поиск по VIN</label>
                <input
                    id="client-vin-search"
                    x-model="vinSearch"
                    @input="onSearch"
                    type="text"
                    placeholder="Поиск по VIN"
                    class="w-full rounded-xl border border-(--client-border) bg-white px-3 py-2 text-sm outline-none focus:border-(--client-primary)"
                />
            </div>

            <div class="relative">
                <button class="client-btn client-btn-outline" type="button" @click="toggleMenu">Меню</button>
                <div
                    x-cloak
                    x-show="menuOpen"
                    x-transition
                    @click.outside="closeMenu"
                    class="absolute right-0 top-[calc(100%+8px)] w-52 rounded-xl border border-(--client-border) bg-white p-2 shadow-xl"
                >
                    <a href="/instructions" class="block rounded-lg px-3 py-2 text-sm hover:bg-slate-100">Инструкция</a>
                    <a href="/profile" class="mt-1 block rounded-lg px-3 py-2 text-sm hover:bg-slate-100">Профиль</a>
                    <form method="POST" action="/logout" class="mt-1">
                        @csrf
                        <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50">Выйти</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-6">
        @if (session('success'))
            <div class="client-card mb-4 border-l-4 border-l-emerald-500 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="client-card mb-4 border-l-4 border-l-red-500 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>
