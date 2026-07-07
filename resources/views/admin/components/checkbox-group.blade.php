<div class="{{ $wrapperClass ?? '' }}">
    @if (isset($label))
        <span class="mb-2 block text-sm font-medium text-slate-700">{{ $label }}</span>
    @endif

    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 md:grid-cols-3">
        @foreach ($options as $optionValue => $optionLabel)
            <label class="flex items-center gap-2 rounded-lg border border-slate-200 p-2 hover:bg-slate-50">
                <input
                    type="checkbox"
                    name="{{ $name }}[]"
                    value="{{ $optionValue }}"
                    {{ in_array((string) $optionValue, (array) old($name, $selected ?? []), true) ? 'checked' : '' }}
                    class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-600"
                />
                <span class="text-sm text-slate-700">{{ $optionLabel }}</span>
            </label>
        @endforeach
    </div>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
