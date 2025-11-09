<template>
  <section>
    <h4 class="text-base font-medium mb-2">Файлы</h4>
    <div class="grid grid-cols-1 gap-3">
      <div class="border-2 border-dashed rounded-lg p-4 bg-white" @dragover.prevent @drop.prevent="$emit('drop-file', $event, 'photos')">
        <div class="text-sm text-gray-600">Фотографии: перетащите сюда или выберите</div>
        <input type="file" accept="image/*" multiple class="hidden" @change="$emit('pick-file', $event, 'photos')" />
        <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
          <div v-for="(f,i) in upload.photos" :key="'p'+i" class="relative">
            <img :src="URL.createObjectURL(f)" class="h-20 w-full object-cover rounded-md border" />
            <button type="button" class="absolute top-1 right-1 rounded bg-white/90 border px-1 text-xs" @click="$emit('remove','photos', i)">×</button>
          </div>
        </div>
        <p v-if="errors && errors['photos.0']" class="mt-2 text-sm text-red-600">{{ errors['photos.0'] }}</p>
      </div>

      <div class="border-2 border-dashed rounded-lg p-4 bg-white" @dragover.prevent @drop.prevent="$emit('drop-file', $event, 'videos')">
        <div class="text-sm text-gray-600">Видео: перетащите сюда или выберите</div>
        <input type="file" accept="video/*" multiple class="hidden" @change="$emit('pick-file', $event, 'videos')" />
        <ul class="mt-2 space-y-1">
          <li v-for="(f,i) in upload.videos" :key="'v'+i" class="flex items-center justify-between rounded-md border p-2 text-sm">
            <span class="truncate mr-3">{{ f.name }}</span>
            <button type="button" class="rounded-md border px-2 py-0.5" @click="$emit('remove','videos', i)">Удалить</button>
          </li>
        </ul>
        <p v-if="errors && errors['videos.0']" class="mt-2 text-sm text-red-600">{{ errors['videos.0'] }}</p>
      </div>

      <div class="border-2 border-dashed rounded-lg p-4 bg-white" @dragover.prevent @drop.prevent="$emit('drop-file', $event, 'documents')">
        <div class="text-sm text-gray-600">Документы: перетащите сюда или выберите</div>
        <input type="file" multiple class="hidden" @change="$emit('pick-file', $event, 'documents')" />
        <ul class="mt-2 space-y-1">
          <li v-for="(f,i) in upload.documents" :key="'d'+i" class="flex items-center justify-between rounded-md border p-2 text-sm">
            <span class="truncate mr-3">{{ f.name }}</span>
            <button type="button" class="rounded-md border px-2 py-0.5" @click="$emit('remove','documents', i)">Удалить</button>
          </li>
        </ul>
        <p v-if="errors && errors['documents.0']" class="mt-2 text-sm text-red-600">{{ errors['documents.0'] }}</p>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  upload: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
})
</script>
