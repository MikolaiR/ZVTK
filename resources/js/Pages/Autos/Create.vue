<template>
  <ClientLayout>
    <div class="max-w-4xl">
      <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Добавить автомобиль</h1>
        <Link href="/autos" class="text-sm rounded-md border px-3 py-1.5 hover:bg-gray-100">К списку</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Бренд</label>
            <select v-model="form.auto_brand_id" @change="onBrandChange" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
              <option :value="null" disabled>Выберите бренд</option>
              <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
            <p v-if="form.errors.auto_brand_id" class="mt-1 text-sm text-red-600">{{ form.errors.auto_brand_id }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Модель</label>
            <select v-model="form.auto_model_id" :disabled="!models.length" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900 disabled:opacity-50">
              <option :value="null" disabled>Выберите модель</option>
              <option v-for="m in models" :key="m.id" :value="m.id">{{ m.name }}</option>
            </select>
            <p v-if="form.errors.auto_model_id" class="mt-1 text-sm text-red-600">{{ form.errors.auto_model_id }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Цвет</label>
            <select v-model="form.color_id" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
              <option :value="null">Не выбран</option>
              <option v-for="c in colors" :key="c.id" :value="c.id" :style="{ '--clr': c.hex_code }"
                      class="text-[var(--clr)]">{{ `${c.name} ${c.name_ru}` }}</option>
            </select>
            <p v-if="form.errors.color_id" class="mt-1 text-sm text-red-600">{{ form.errors.color_id }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">VIN</label>
            <input v-model="form.vin" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900" />
            <p v-if="form.errors.vin" class="mt-1 text-sm text-red-600">{{ form.errors.vin }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Дата отправки</label>
            <input v-model="form.departure_date" type="date" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900" />
            <p v-if="form.errors.departure_date" class="mt-1 text-sm text-red-600">{{ form.errors.departure_date }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Год производства</label>
            <input v-model.number="form.year" type="number" :max="currentYear" min="1900" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900" />
            <p v-if="form.errors.year" class="mt-1 text-sm text-red-600">{{ form.errors.year }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Цена</label>
            <input v-model.number="form.price" type="number" min="0" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900" />
            <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
          </div>

          <div class="md:col-span-2">
            <div class="text-xs text-gray-500">Заголовок будет сформирован автоматически: бренд + модель + VIN</div>
          </div>
        </div>

        <!-- Photos -->
        <section>
          <h2 class="text-base font-medium mb-2">Фотографии</h2>
          <div
            class="border-2 border-dashed rounded-lg p-4 bg-white"
            @dragover.prevent
            @drop.prevent="onDrop($event, 'photos')"
          >
            <div class="text-sm text-gray-600">Перетащите файлы сюда или выберите</div>
            <input ref="photosInput" type="file" accept="image/*" multiple class="hidden" @change="onFilesSelected($event, 'photos')" />
            <button type="button" class="mt-2 rounded-md border px-3 py-1.5 hover:bg-gray-50" @click="() => photosInput.click()">Выбрать файлы</button>

            <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
              <div v-for="(file, idx) in form.photos" :key="idx" class="relative group">
                <img :src="getPreview(file)" class="h-28 w-full object-cover rounded-md border" />
                <button type="button" class="absolute top-1 right-1 rounded-md bg-white/90 border px-2 py-0.5 text-xs shadow hover:bg-white" @click="removeFile('photos', idx)">Удалить</button>
              </div>
            </div>
            <p v-if="form.errors['photos.0']" class="mt-2 text-sm text-red-600">{{ form.errors['photos.0'] }}</p>
          </div>
        </section>

        <!-- Videos -->
        <section>
          <h2 class="text-base font-medium mb-2">Видео</h2>
          <div class="border-2 border-dashed rounded-lg p-4 bg-white" @dragover.prevent @drop.prevent="onDrop($event, 'videos')">
            <div class="text-sm text-gray-600">Перетащите файлы сюда или выберите</div>
            <input ref="videosInput" type="file" accept="video/*" multiple class="hidden" @change="onFilesSelected($event, 'videos')" />
            <button type="button" class="mt-2 rounded-md border px-3 py-1.5 hover:bg-gray-50" @click="() => videosInput.click()">Выбрать файлы</button>

            <ul class="mt-3 space-y-2">
              <li v-for="(file, idx) in form.videos" :key="idx" class="flex items-center justify-between rounded-md border p-2 text-sm">
                <span class="truncate mr-3">{{ file.name }}</span>
                <button type="button" class="rounded-md border px-2 py-0.5 hover:bg-gray-50" @click="removeFile('videos', idx)">Удалить</button>
              </li>
            </ul>
            <p v-if="form.errors['videos.0']" class="mt-2 text-sm text-red-600">{{ form.errors['videos.0'] }}</p>
          </div>
        </section>

        <!-- Documents -->
        <section>
          <h2 class="text-base font-medium mb-2">Документы</h2>
          <div class="border-2 border-dashed rounded-lg p-4 bg-white" @dragover.prevent @drop.prevent="onDrop($event, 'documents')">
            <div class="text-sm text-gray-600">Перетащите файлы сюда или выберите</div>
            <input ref="docsInput" type="file" multiple class="hidden" @change="onFilesSelected($event, 'documents')" />
            <button type="button" class="mt-2 rounded-md border px-3 py-1.5 hover:bg-gray-50" @click="() => docsInput.click()">Выбрать файлы</button>

            <ul class="mt-3 space-y-2">
              <li v-for="(file, idx) in form.documents" :key="idx" class="flex items-center justify-between rounded-md border p-2 text-sm">
                <span class="truncate mr-3">{{ file.name }}</span>
                <button type="button" class="rounded-md border px-2 py-0.5 hover:bg-gray-50" @click="removeFile('documents', idx)">Удалить</button>
              </li>
            </ul>
            <p v-if="form.errors['documents.0']" class="mt-2 text-sm text-red-600">{{ form.errors['documents.0'] }}</p>
          </div>
        </section>

        <div class="flex items-center gap-3">
          <button type="submit" :disabled="form.processing" class="rounded-md bg-gray-900 px-4 py-2 text-white hover:bg-black disabled:opacity-50">Добавить</button>
          <span v-if="form.progress" class="text-sm text-gray-600">Загрузка: {{ form.progress.percentage }}%</span>
        </div>
      </form>
    </div>
  </ClientLayout>
</template>

<script setup>
import ClientLayout from '../../Layouts/ClientLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import { ref, computed, onBeforeUnmount } from 'vue'

const props = defineProps({
  brands: { type: Array, required: true },
  colors: { type: Array, required: true },
})

const currentYear = new Date().getFullYear()
const models = ref([])

const form = useForm({
  departure_date: null,
  auto_brand_id: null,
  auto_model_id: null,
  color_id: null,
  vin: '',
  year: null,
  price: null,
  photos: [],
  videos: [],
  documents: [],
})

const photosInput = ref(null)
const videosInput = ref(null)
const docsInput = ref(null)

const onBrandChange = async () => {
  form.auto_model_id = null
  models.value = []
  if (!form.auto_brand_id) return
  const { data } = await axios.get(`/api/brands/${form.auto_brand_id}/models`)
  models.value = data
}

const appendFiles = (kind, fileList) => {
  const arr = Array.from(fileList || [])
  form[kind] = [...form[kind], ...arr]
}

const onFilesSelected = (e, kind) => {
  appendFiles(kind, e.target.files)
  e.target.value = ''
}

const onDrop = (e, kind) => {
  appendFiles(kind, e.dataTransfer.files)
}

const getPreview = (file) => URL.createObjectURL(file)

const removeFile = (kind, idx) => {
  form[kind].splice(idx, 1)
}

onBeforeUnmount(() => {
  // best-effort: revoke previews that may be around
  ;['photos'].forEach((kind) => {
    form[kind].forEach((f) => URL.revokeObjectURL && URL.revokeObjectURL(f.preview || ''))
  })
})

const submit = () => {
  form.post('/autos', { forceFormData: true })
}
</script>
