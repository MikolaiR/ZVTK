@php
    $variant = $variant ?? 'primary';
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2';
    $styles = [
        'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-600',
        'secondary' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus:ring-blue-600',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-600',
    ];
@endphp

@if (isset($href))
    <a href="{{ $href }}" class="{{ $base }} {{ $styles[$variant] ?? $styles['primary'] }}">
        {{ $slot }}
    </a>
@else
    <button type="{{ $type ?? 'button' }}" class="{{ $base }} {{ $styles[$variant] ?? $styles['primary'] }}">
        {{ $slot }}
    </button>
@endif
