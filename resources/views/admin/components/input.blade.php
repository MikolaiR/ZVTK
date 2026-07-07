<div class="{{ $wrapperClass ?? '' }}">
    @if (isset($label))
        <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-slate-700">
            {{ $label }}
            @if (isset($required) && $required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type ?? 'text' }}"
        value="{{ old($name, $value ?? '') }}"
        @isset($placeholder) placeholder="{{ $placeholder }}" @endisset
        @isset($autofocus) autofocus @endisset
        @isset($required) required @endisset
        @isset($step) step="{{ $step }}" @endisset
        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error($name) border-red-300 focus:border-red-600 focus:ring-red-600 @enderror"
    />

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
