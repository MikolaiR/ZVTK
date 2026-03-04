<div
    class="rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50/70 p-4"
    @dragover.prevent
    @drop.prevent="onDrop"
>
    <div class="text-center">
        <p class="text-sm text-slate-600">Перетащите файлы сюда или</p>
        <button
            type="button"
            class="mt-2 rounded-md border border-slate-400 bg-white px-3 py-1.5 text-sm hover:bg-slate-50"
            @click="openPicker"
        >
            Выбрать файлы
        </button>
        <input
            x-ref="picker"
            type="file"
            multiple
            accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx"
            class="hidden"
            @change="onPick"
        >
    </div>

    <div class="mt-3 space-y-1 text-left">
        @error('photos.0') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        @error('videos.0') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        @error('documents.0') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div x-show="uploading" class="mt-3">
        <div class="mb-1 flex items-center justify-between text-xs text-slate-500">
            <span>Загрузка файлов</span>
            <span x-text="`${uploadProgress}%`"></span>
        </div>
        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
            <div class="h-full rounded-full bg-(--client-primary) transition-all duration-150" :style="`width:${uploadProgress}%`"></div>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4" x-show="fileQueue.length">
        <template x-for="item in fileQueue" :key="item.id">
            <div class="relative overflow-hidden rounded-lg border border-slate-300 bg-white">
                <template x-if="item.kind === 'photos'">
                    <img :src="item.preview" alt="preview" class="h-24 w-full object-cover">
                </template>

                <template x-if="item.kind === 'videos'">
                    <video :src="item.preview" controls class="h-24 w-full"></video>
                </template>

                <template x-if="item.kind === 'documents'">
                    <div class="flex h-24 w-full flex-col items-center justify-center bg-slate-100 px-2 text-center">
                        <span class="text-sm font-semibold">DOC</span>
                        <span class="mt-1 w-full truncate text-[11px] text-slate-600" x-text="item.name"></span>
                    </div>
                </template>

                <button
                    type="button"
                    class="absolute right-1 top-1 rounded border bg-white/90 px-1 text-xs"
                    @click="removeFile(item.id)"
                    aria-label="Удалить файл"
                >×</button>
            </div>
        </template>
    </div>

    <p class="mt-3 text-xs text-slate-500">
        Добавлено: <span x-text="countByKind('photos')"></span> фото,
        <span x-text="countByKind('videos')"></span> видео,
        <span x-text="countByKind('documents')"></span> документов.
    </p>
</div>

<input x-ref="photosInput" type="file" name="photos[]" multiple class="hidden">
<input x-ref="videosInput" type="file" name="videos[]" multiple class="hidden">
<input x-ref="documentsInput" type="file" name="documents[]" multiple class="hidden">
