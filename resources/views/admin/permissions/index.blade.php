@extends('admin.layouts.app')

@section('title', 'Разрешения')

@section('content')
    @component('admin.components.page-header', [
        'title' => 'Разрешения',
        'subtitle' => 'Управление разрешениями и ролями',
    ])
        @slot('actions')
            @include('admin.components.button', [
                'href' => route('admin.permissions.create'),
                'slot' => 'Добавить разрешение',
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
                        <th class="px-4 py-3">Роли</th>
                        <th class="px-4 py-3 text-right">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($permissions as $permission)
                        <tr>
                            <td class="px-4 py-3">{{ $permission['id'] }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $permission['name'] }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($permission['roles'] as $roleName)
                                        <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $roleName }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.permissions.edit', $permission['id']) }}" class="mr-3 text-sm font-medium text-blue-600 hover:text-blue-700">Изменить</a>
                                @include('admin.components.delete-form', [
                                    'action' => route('admin.permissions.destroy', $permission['id']),
                                    'confirm' => 'Удалить разрешение?',
                                    'slot' => 'Удалить',
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                                @include('admin.components.empty-state', ['message' => 'Разрешения не найдены'])
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('admin.components.pagination', ['paginator' => $permissions])
    @endcomponent
@endsection
