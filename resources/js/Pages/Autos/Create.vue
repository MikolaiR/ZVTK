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

        <Uploads :upload="upload" :errors="form.errors" @drop-file="onDrop" @pick-file="onFilesSelected" @remove="removeFile" />

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
import Uploads from '../../Components/Uploads.vue'
import axios from 'axios'
import { ref, computed, reactive } from 'vue'

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

const upload = reactive({ photos: [], videos: [], documents: [] })

const onBrandChange = async () => {
  form.auto_model_id = null
  models.value = []
  if (!form.auto_brand_id) return
  const { data } = await axios.get(`/api/brands/${form.auto_brand_id}/models`)
  models.value = data
}

const appendFiles = (kind, fileList) => {
  const arr = Array.from(fileList || [])
  upload[kind] = [...upload[kind], ...arr]
}

const onFilesSelected = (e, kind) => {
  appendFiles(kind, e.target.files)
  e.target.value = ''
}

const onDrop = (e, kind) => {
  appendFiles(kind, e.dataTransfer.files)
}

const removeFile = (kind, idx) => {
  upload[kind].splice(idx, 1)
}

const syncUploadsToForm = () => {
  form.photos = upload.photos
  form.videos = upload.videos
  form.documents = upload.documents
}

const submit = () => {
  syncUploadsToForm()
  form
    .transform((data) => {
      const fd = new FormData()
      Object.entries(data).forEach(([key, value]) => {
        if (Array.isArray(value) && value.length && value[0] instanceof File) {
          value.forEach((f) => fd.append(`${key}[]`, f))
        } else if (Array.isArray(value)) {
          value.forEach((v) => fd.append(`${key}[]`, v ?? ''))
        } else if (value === null || value === undefined) {
          fd.append(key, '')
        } else {
          fd.append(key, value)
        }
      })
      return fd
    })
    .post('/autos')
}
</script>
