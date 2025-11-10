<template>
  <section>
    <h4 class="text-base font-medium mb-2">Файлы</h4>
    <div class="border-2 border-dashed rounded-lg p-4 bg-white text-center" @dragover.prevent @drop.prevent="onDrop">
      <div class="text-sm text-gray-600">Перетащите файлы сюда или</div>
      <button type="button" class="mt-2 rounded-md border px-3 py-1.5 hover:bg-gray-50" @click="openPicker">Выбрать файлы</button>
      <input ref="fileInput" type="file" multiple accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx" class="hidden" @change="onInputChange" />

      <div v-if="allFiles.length" class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
        <div v-for="(f,i) in allFiles" :key="i" class="relative">
          <template v-if="isImage(f)">
            <img :src="previewUrl(f)" class="h-24 w-full object-cover rounded-md border" />
          </template>
          <template v-else-if="isVideo(f)">
            <video :src="previewUrl(f)" class="h-24 w-full rounded-md border" controls />
          </template>
          <template v-else>
            <div class="h-24 w-full rounded-md border bg-gray-100 flex items-center justify-center text-sm font-semibold">DOC</div>
          </template>
          <button type="button" class="absolute top-1 right-1 rounded bg-white/90 border px-1 text-xs" @click="onRemoveFile(f)">×</button>
        </div>
      </div>

      <div class="mt-3 space-y-1 text-left">
        <p v-if="errors && errors['photos.0']" class="text-sm text-red-600">{{ errors['photos.0'] }}</p>
        <p v-if="errors && errors['videos.0']" class="text-sm text-red-600">{{ errors['videos.0'] }}</p>
        <p v-if="errors && errors['documents.0']" class="text-sm text-red-600">{{ errors['documents.0'] }}</p>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed } from 'vue'
const props = defineProps({
  upload: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
})
const emit = defineEmits(['drop-file','pick-file','remove'])

const fileInput = ref(null)
const openPicker = () => { if (fileInput.value) fileInput.value.click() }

const allFiles = computed(() => [
  ...(props.upload.photos || []),
  ...(props.upload.videos || []),
  ...(props.upload.documents || []),
])

const isImage = (f) => ((f && f.type) || '').startsWith('image/')
const isVideo = (f) => ((f && f.type) || '').startsWith('video/')
const kindFor = (f) => (isImage(f) ? 'photos' : isVideo(f) ? 'videos' : 'documents')

const previewUrl = (f) => {
  try {
    if (typeof window !== 'undefined' && window.URL && window.URL.createObjectURL) {
      return window.URL.createObjectURL(f)
    }
  } catch (e) {}
  return ''
}

const onRemoveFile = (f) => {
  const kind = kindFor(f)
  const idx = (props.upload[kind] || []).indexOf(f)
  if (idx >= 0) emit('remove', kind, idx)
}

const classify = (files) => {
  const res = { photos: [], videos: [], documents: [] }
  Array.from(files || []).forEach((f) => {
    const k = kindFor(f)
    res[k].push(f)
  })
  return res
}

const emitGroup = (eventName, kind, items) => {
  if (!items.length) return
  const ev = { dataTransfer: { files: items }, target: { files: items }, preventDefault: () => {} }
  emit(eventName, ev, kind)
}

const onInputChange = (e) => {
  const g = classify(e.target.files)
  emitGroup('pick-file', 'photos', g.photos)
  emitGroup('pick-file', 'videos', g.videos)
  emitGroup('pick-file', 'documents', g.documents)
  // clear the real input so the same files can be re-selected and to avoid stale state
  if (fileInput.value) fileInput.value.value = ''
}

const onDrop = (e) => {
  const g = classify((e.dataTransfer && e.dataTransfer.files) || [])
  emitGroup('drop-file', 'photos', g.photos)
  emitGroup('drop-file', 'videos', g.videos)
  emitGroup('drop-file', 'documents', g.documents)
}
</script>
