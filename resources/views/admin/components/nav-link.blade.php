@php
    $isActive = request()->routeIs($route) || request()->routeIs($route . '.*');
@endphp

<a
    href="{{ route($route) }}"
    class="block rounded-lg px-3 py-2 text-sm font-medium transition {{ $isActive ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
>
    {{ $label }}
</a>
