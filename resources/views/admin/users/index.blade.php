@extends('admin.layouts.app')

@section('title', 'Пользователи')

@section('content')
    @component('admin.components.page-header', [
        'title' => 'Пользователи',
        'subtitle' => 'Управление пользователями, ролями и статусами',
    ])
        @slot('actions')
            @include('admin.components.button', [
                'href' => route('admin.users.create'),
                'slot' => 'Добавить пользователя',
            ])
        @endslot
    @endcomponent

    @component('admin.components.card')
        <div class="p-4">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Поиск по имени или email"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600"
                    />
                </div>

                <select
                    name="status"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600"
                >
                    <option value="all" {{ ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' }}>Все статусы</option>
                    <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Активные</option>
                    <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Неактивные</option>
                    <option value="deleted" {{ ($filters['status'] ?? '') === 'deleted' ? 'selected' : '' }}>Удалённые</option>
                </select>

                <select
                    name="role"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600"
                >
                    <option value="" {{ ($filters['role'] ?? '') === '' ? 'selected' : '' }}>Все роли</option>
                    @foreach ($roles as $roleName)
                        <option value="{{ $roleName }}" {{ ($filters['role'] ?? '') === $roleName ? 'selected' : '' }}>{{ $roleName }}</option>
                    @endforeach
                </select>

                <button type="submit" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">Фильтр</button>
                @if ($filters['search'] ?? $filters['status'] !== 'all' ?? $filters['role'] ?? false)
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Сбросить</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Пользователь</th>
                        <th class="px-4 py-3">Компания</th>
                        <th class="px-4 py-3">Роли</th>
                        <th class="px-4 py-3">Статус</th>
                        <th class="px-4 py-3 text-right">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <tr class="{{ $user['deleted_at'] ? 'bg-slate-50 opacity-75' : '' }}">
                            <td class="px-4 py-3">{{ $user['id'] }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">{{ $user['name'] }}</div>
                                <div class="text-xs text-slate-500">{{ $user['email'] }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $user['company']['name'] ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($user['roles'] as $roleName)
                                        <span class="inline-flex rounded-full bg-blue-50 px-2 py-0.5 text-xs text-blue-700">{{ $roleName }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if ($user['deleted_at'])
                                    <span class="inline-flex rounded-full bg-red-50 px-2 py-0.5 text-xs text-red-700">Удалён</span>
                                @elseif ($user['is_active'])
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">Активен</span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">Неактивен</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if ($user['deleted_at'])
                                    <form method="POST" action="{{ route('admin.users.restore', $user['id']) }}" class="inline-block">
                                        @csrf
                                        <button type="submit" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">Восстановить</button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.users.edit', $user['id']) }}" class="mr-3 text-sm font-medium text-blue-600 hover:text-blue-700">Изменить</a>
                                    @include('admin.components.delete-form', [
                                        'action' => route('admin.users.destroy', $user['id']),
                                        'confirm' => 'Удалить пользователя?',
                                        'slot' => 'Удалить',
                                    ])
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                @include('admin.components.empty-state', ['message' => 'Пользователи не найдены'])
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('admin.components.pagination', ['paginator' => $users])
    @endcomponent
@endsection
