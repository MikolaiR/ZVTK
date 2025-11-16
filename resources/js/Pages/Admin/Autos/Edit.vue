<template>
  <AdminLayout>
    <div class="max-w-4xl">
      <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Редактирование авто</h1>
        <Link href="/admin/autos" class="text-sm rounded-md border px-3 py-1.5 hover:bg-gray-100">К списку</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <FormSelect
            label="Бренд"
            v-model="form.auto_brand_id"
            :options="brandOptions"
            placeholder="Выберите бренд"
            :error="form.errors.auto_brand_id"
            @update:modelValue="onBrandChange"
          />

          <FormSelect
            label="Модель"
            v-model="form.auto_model_id"
            :options="modelOptions"
            placeholder="Выберите модель"
            :error="form.errors.auto_model_id"
            :disabled="!models.length"
          />

          <FormSelect
            label="Цвет"
            v-model="form.color_id"
            :options="colorOptions"
            placeholder="Не выбран"
            :error="form.errors.color_id"
          />

          <FormSelect
            label="Компания"
            v-model.number="form.company_id"
            :options="companyOptions"
            placeholder="Не выбрана"
            :error="form.errors.company_id"
          />

          <FormSelect
            label="Отправитель"
            v-model.number="form.sender_id"
            :options="senderOptions"
            placeholder="Не выбран"
            :error="form.errors.sender_id"
          />

          <FormSelect
            label="Перевозчик"
            v-model.number="form.provider_id"
            :options="providerOptions"
            placeholder="Не выбран"
            :error="form.errors.provider_id"
          />

          <FormInput
            label="Заголовок"
            v-model="form.title"
            :error="form.errors.title"
            hint="Пустой — будет сформирован автоматически (бренд + модель + VIN)"
          />

          <FormInput label="VIN" v-model="form.vin" :error="form.errors.vin" />

          <FormInput label="Дата отправки" type="date" v-model="form.departure_date" :error="form.errors.departure_date" />

          <FormInput label="Год" type="number" :max="currentYear" min="1900" v-model.number="form.year" :error="form.errors.year" />

          <FormInput label="Цена" type="number" min="0" v-model.number="form.price" :error="form.errors.price" />

          <FormSelect label="Статус" v-model.number="form.status" :options="statusOptions" />
        </div>

        <section>
          <h4 class="text-base font-medium mb-2">Текущие файлы</h4>
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
            <div v-for="m in allMedia" :key="m.id" class="relative">
              <template v-if="m.kind === 'photo'">
                <img :src="m.url" class="h-24 w-full object-cover rounded-md border" />
              </template>
              <template v-else-if="m.kind === 'video'">
                <video :src="m.url" class="h-24 w-full rounded-md border" controls />
              </template>
              <template v-else>
                <a :href="m.url" target="_blank" rel="noopener" class="block h-24 w-full rounded-md border bg-gray-100 flex items-center justify-center text-sm font-semibold hover:bg-gray-200">DOC</a>
              </template>
              <button type="button" class="absolute top-1 right-1 rounded bg-white/90 border px-1 text-xs"
                :class="{ 'bg-red-50 border-red-300 text-red-700': removeSet.has(m.id) }"
                @click="toggleRemove(m.id)">
                {{ removeSet.has(m.id) ? 'Отмена' : 'Удалить' }}
              </button>
            </div>
          </div>
        </section>

        <section>
          <h4 class="text-base font-medium mb-2">Периоды локаций</h4>

          <div class="overflow-x-auto rounded-lg border bg-white mb-4">
            <table class="min-w-full text-sm">
              <thead class="bg-gray-50 text-left">
                <tr>
                  <th class="px-3 py-2">Статус</th>
                  <th class="px-3 py-2">Место</th>
                  <th class="px-3 py-2">Начало</th>
                  <th class="px-3 py-2">Окончание</th>
                  <th class="px-3 py-2">Кто принял</th>
                  <th class="px-3 py-2">Заметка</th>
                  <th class="px-3 py-2 text-right">Действия</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in periods" :key="p.id" class="border-t align-top">
                  <td class="px-3 py-2">
                    <template v-if="editingId === p.id">
                      <FormSelect v-model.number="editForm.status" :options="statusOptions" />
                    </template>
                    <template v-else>
                      <span class="inline-flex items-center rounded bg-gray-100 px-2 py-0.5">{{ p.status_label }}</span>
                      <span v-if="!p.ended_at" class="ml-2 text-xs text-emerald-700">Активен</span>
                    </template>
                  </td>
                  <td class="px-3 py-2">
                    <template v-if="editingId === p.id">
                      <FormSelect
                        v-if="editShowLocationSelect"
                        :label="editLocationLabel"
                        v-model.number="editForm.location_id"
                        :options="editLocationOptions"
                        placeholder="Выберите место"
                      />
                      <span v-else class="text-xs text-gray-500">Место определяется автоматически</span>
                    </template>
                    <template v-else>
                      {{ p.type_label }}<template v-if="p.name">: {{ p.name }}</template>
                    </template>
                  </td>
                  <td class="px-3 py-2">
                    <template v-if="editingId === p.id">
                      <FormInput type="datetime-local" v-model="editForm.started_at" />
                    </template>
                    <template v-else>{{ p.started_at || '—' }}</template>
                  </td>
                  <td class="px-3 py-2">
                    <template v-if="editingId === p.id">
                      <div class="flex items-center gap-2">
                        <FormInput type="datetime-local" v-model="editForm.ended_at" />
                        <AppButton variant="outline" @click.prevent="editForm.ended_at = null">Очистить</AppButton>
                      </div>
                    </template>
                    <template v-else>{{ p.ended_at || '—' }}</template>
                  </td>
                  <td class="px-3 py-2">
                    <template v-if="editingId === p.id">
                      <FormSelect v-model.number="editForm.accepted_by_user_id" :options="userOptions" placeholder="Не выбран" />
                    </template>
                    <template v-else>{{ p.accepted_by?.name || '—' }}</template>
                  </td>
                  <td class="px-3 py-2">
                    <template v-if="editingId === p.id">
                      <FormTextarea v-model="editForm.note" />
                    </template>
                    <template v-else>{{ p.acceptance_note || '—' }}</template>
                  </td>
                  <td class="px-3 py-2 text-right">
                    <div class="inline-flex items-center gap-2">
                      <template v-if="editingId === p.id">
                        <AppButton @click.prevent="saveEdit(p)">Сохранить</AppButton>
                        <AppButton variant="outline" @click.prevent="cancelEdit">Отмена</AppButton>
                      </template>
                      <template v-else>
                        <AppButton variant="outline" @click.prevent="startEdit(p)">Редактировать</AppButton>
                        <AppButton v-if="!p.ended_at" variant="outline" @click.prevent="closePeriod(p)">Закрыть</AppButton>
                        <AppButton variant="danger" @click.prevent="deletePeriod(p)">Удалить</AppButton>
                      </template>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <FormSelect
              label="Статус"
              v-model.number="periodForm.status"
              :options="allowedPeriodStatuses"
              placeholder="Выберите статус"
              :error="periodForm.errors.status"
              @update:modelValue="onStatusChange"
            />

            <FormSelect
              v-if="showLocationSelect"
              :label="locationLabel"
              v-model.number="periodForm.location_id"
              :options="locationOptions"
              placeholder="Выберите место"
              :error="periodForm.errors.location_id"
            />

            <FormSelect
              label="Кто принял"
              v-model.number="periodForm.accepted_by_user_id"
              :options="userOptions"
              placeholder="Не выбран"
              :error="periodForm.errors.accepted_by_user_id"
            />

            <FormTextarea label="Заметка" v-model="periodForm.note" :error="periodForm.errors.note" />
          </div>

          <div class="mt-3">
            <AppButton @click.prevent="createPeriod" :disabled="periodForm.processing">Добавить период</AppButton>
          </div>
        </section>

        <Uploads :upload="upload" :errors="form.errors" @drop-file="onDrop" @pick-file="onFilesSelected" @remove="removeFile" />

        <div class="flex items-center gap-3">
          <AppButton type="submit" :disabled="form.processing">Сохранить</AppButton>
          <span v-if="form.progress" class="text-sm text-gray-600">Загрузка: {{ form.progress.percentage }}%</span>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '../../../Layouts/AdminLayout.vue'
import { Link, useForm, usePage, router } from '@inertiajs/vue3'
import Uploads from '../../../Components/Uploads.vue'
import axios from 'axios'
import { ref, reactive, computed, onMounted } from 'vue'
import FormInput from '../../../Components/FormInput.vue'
import FormSelect from '../../../Components/FormSelect.vue'
import AppButton from '../../../Components/AppButton.vue'
import FormTextarea from '../../../Components/FormTextarea.vue'

const page = usePage()
const auto = page.props.auto || {}
const reactiveAuto = computed(() => page.props.auto || {})
const brands = page.props.brands || []
const colors = page.props.colors || []
const statuses = page.props.statuses || []
const customers = page.props.customers || []
const parkings = page.props.parkings || []
const companies = page.props.companies || []
const senders = page.props.senders || []
const providers = page.props.providers || []
const users = page.props.users || []

const currentYear = new Date().getFullYear()
const models = ref([])

const brandOptions = computed(() => (brands || []).map(b => ({ label: b.name, value: b.id })))
const modelOptions = computed(() => (models.value || []).map(m => ({ label: m.name, value: m.id })))
const colorOptions = computed(() => (colors || []).map(c => ({ label: `${c.name} ${c.name_ru}`, value: c.id })))
const statusOptions = computed(() => (statuses || []).map(s => ({ label: s.label, value: s.value })))
const allowedPeriodStatuses = computed(() => (statuses || []).filter(s => s.value !== 5).map(s => ({ label: s.label, value: s.value })))
const customerOptions = computed(() => (customers || []).map(c => ({ label: c.name, value: c.id })))
const parkingOptions = computed(() => (parkings || []).map(p => ({ label: p.name, value: p.id })))
const companyOptions = computed(() => (companies || []).map(c => ({ label: c.name, value: c.id })))
const senderOptions = computed(() => (senders || []).map(s => ({ label: s.name, value: s.id })))
const providerOptions = computed(() => (providers || []).map(p => ({ label: p.name, value: p.id })))
const userOptions = computed(() => (users || []).map(u => ({ label: u.name, value: u.id })))

const form = useForm({
  title: auto.title || '',
  departure_date: auto.departure_date || null,
  auto_brand_id: auto.auto_brand_id || null,
  auto_model_id: auto.auto_model_id || null,
  color_id: auto.color_id || null,
  company_id: auto.company_id || null,
  sender_id: auto.sender_id || null,
  provider_id: auto.provider_id || null,
  vin: auto.vin || '',
  year: auto.year || null,
  price: auto.price || null,
  status: auto.status,
  remove_media: [],
  photos: [],
  videos: [],
  documents: [],
})

const upload = reactive({ photos: [], videos: [], documents: [] })
const removeSet = ref(new Set())

const allMedia = computed(() => {
  const res = []
  ;(auto.media?.photos || []).forEach((m) => res.push({ ...m, kind: 'photo' }))
  ;(auto.media?.videos || []).forEach((m) => res.push({ ...m, kind: 'video' }))
  ;(auto.media?.documents || []).forEach((m) => res.push({ ...m, kind: 'doc' }))
  return res
})

const periods = computed(() => Array.isArray(reactiveAuto.value?.periods) ? reactiveAuto.value.periods : [])

const periodForm = useForm({ status: null, location_id: null, accepted_by_user_id: null, note: '' })

// Helpers for datetime conversion
const toLocalInput = (s) => {
  if (!s) return ''
  // Expecting 'YYYY-MM-DD HH:MM:SS' -> 'YYYY-MM-DDTHH:MM'
  return String(s).replace(' ', 'T').slice(0, 16)
}
const toApiDateTime = (s) => {
  if (!s) return null
  // 'YYYY-MM-DDTHH:MM' -> 'YYYY-MM-DD HH:MM:00'
  const v = String(s).replace('T', ' ')
  return v.length === 16 ? `${v}:00` : v
}

// Inline edit state
const editingId = ref(null)
const editForm = useForm({ status: null, location_id: null, started_at: '', ended_at: null, note: '', accepted_by_user_id: null })

const editShowLocationSelect = computed(() => {
  return editForm.status === STATUS.Customer || editForm.status === STATUS.DeliveryToParking || editForm.status === STATUS.Parking
})
const editLocationLabel = computed(() => (editForm.status === STATUS.Customer ? 'Таможня' : 'Стоянка'))
const editLocationOptions = computed(() => (editForm.status === STATUS.Customer ? customerOptions.value : parkingOptions.value))

const startEdit = (p) => {
  editingId.value = p.id
  editForm.status = p.status
  editForm.location_id = p.location_id ?? null
  editForm.started_at = toLocalInput(p.started_at)
  editForm.ended_at = p.ended_at ? toLocalInput(p.ended_at) : null
  editForm.note = p.acceptance_note || ''
  editForm.accepted_by_user_id = p.accepted_by?.id ?? null
}

const cancelEdit = () => {
  editingId.value = null
  editForm.reset('status', 'location_id', 'started_at', 'ended_at', 'note', 'accepted_by_user_id')
}

const saveEdit = (p) => {
  const payload = {
    status: editForm.status,
    location_id: editForm.location_id,
    started_at: toApiDateTime(editForm.started_at),
    ended_at: toApiDateTime(editForm.ended_at),
    note: editForm.note,
    accepted_by_user_id: editForm.accepted_by_user_id,
  }
  router.put(`/admin/autos/${auto.id}/periods/${p.id}`, payload, {
    preserveScroll: true,
    onSuccess: () => { editingId.value = null; router.reload({ only: ['auto'] }) },
  })
}

const STATUS = { Delivery: 1, Customer: 2, DeliveryToParking: 3, Parking: 4, Sale: 5 }

const showLocationSelect = computed(() => {
  return periodForm.status === STATUS.Customer || periodForm.status === STATUS.DeliveryToParking || periodForm.status === STATUS.Parking
})
const locationLabel = computed(() => (periodForm.status === STATUS.Customer ? 'Таможня' : 'Стоянка'))
const locationOptions = computed(() => (periodForm.status === STATUS.Customer ? customerOptions.value : parkingOptions.value))

const onStatusChange = () => { periodForm.location_id = null }

const createPeriod = () => {
  periodForm.post(`/admin/autos/${auto.id}/periods`, {
    preserveScroll: true,
    onSuccess: () => { periodForm.reset('status', 'location_id', 'note'); router.reload({ only: ['auto'] }) },
  })
}

const closePeriod = (p) => {
  router.post(`/admin/autos/${auto.id}/periods/${p.id}/close`, {}, { preserveScroll: true, onSuccess: () => router.reload({ only: ['auto'] }) })
}

const deletePeriod = (p) => {
  router.delete(`/admin/autos/${auto.id}/periods/${p.id}`, { preserveScroll: true, onSuccess: () => router.reload({ only: ['auto'] }) })
}

const onBrandChange = async (val = null) => {
  if (val !== null) form.auto_brand_id = val
  form.auto_model_id = null
  models.value = []
  if (!form.auto_brand_id) return
  const { data } = await axios.get(`/api/brands/${form.auto_brand_id}/models`)
  models.value = data
}

onMounted(async () => {
  if (form.auto_brand_id) {
    const { data } = await axios.get(`/api/brands/${form.auto_brand_id}/models`)
    models.value = data
  }
})

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

const toggleRemove = (id) => {
  if (removeSet.value.has(id)) removeSet.value.delete(id)
  else removeSet.value.add(id)
}

const syncUploadsToForm = () => {
  form.photos = upload.photos
  form.videos = upload.videos
  form.documents = upload.documents
  form.remove_media = Array.from(removeSet.value)
}

const submit = () => {
  syncUploadsToForm()
  form
    .transform((data) => {
      const fd = new FormData()
      fd.append('_method', 'put')
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
    .post(`/admin/autos/${auto.id}`)
}
</script>
