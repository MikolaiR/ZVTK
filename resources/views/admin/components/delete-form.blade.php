<form
    method="POST"
    action="{{ $action }}"
    onsubmit="return confirm('{{ $confirm ?? 'Вы уверены?' }}');"
    class="inline-block"
>
    @csrf
    @method('DELETE')

    <button
        type="submit"
        class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50"
    >
        {{ $slot }}
    </button>
</form>
