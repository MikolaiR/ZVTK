<template>
  <ClientLayout>
    <div>
      <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold">{{ auto.title || 'Автомобиль' }}</h1>
        <ClientButton href="/autos" variant="outline" class="text-sm">К списку</ClientButton>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Actions (mobile first) -->
        <aside class="order-1 md:order-2 md:col-span-1">
          <div class="sticky top-20 space-y-3">
            <Card title="Статус">
              <StatusBadge :status="auto.status" :label="auto.status_label" />
            </Card>

            <Card title="Действия">
              <AutoActionsPanel :actions="actions" @action="handleAction" />
            </Card>
          </div>
        </aside>

        <!-- Main content -->
        <section class="order-2 md:order-1 md:col-span-2 space-y-6">
          <!-- Media slider -->
          <Card title="Медиа">
            <div class="text-sm text-gray-500 mb-3" v-if="slides.length">{{ slideIndex + 1 }} / {{ slides.length }}</div>
            <div v-if="slides.length" class="relative">
              <div class="aspect-video w-full overflow-hidden rounded-md border bg-black">
                <template v-if="currentSlide.kind === 'photo'">
                  <img :src="currentSlide.url" class="h-full w-full object-contain bg-black" />
                </template>
                <template v-else>
                  <video :src="currentSlide.url" class="h-full w-full" controls />
                </template>
              </div>
              <button @click="prev" class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-2 shadow hover:bg-white">‹</button>
              <button @click="next" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-2 shadow hover:bg-white">›</button>
            </div>
            <div v-else class="aspect-video w-full rounded-md border bg-gray-100 flex items-center justify-center text-gray-500">
              Нет медиа
            </div>

            <!-- Thumbs -->
            <div v-if="slides.length" class="mt-3 grid grid-cols-5 sm:grid-cols-8 md:grid-cols-10 gap-2">
              <button
                v-for="(s, i) in slides"
                :key="s.kind + ':' + s.id + ':' + i"
                @click="go(i)"
                :class="[
                  'h-16 overflow-hidden rounded-md border',
                  i === slideIndex ? 'ring-2 ring-[var(--primary)]' : ''
                ]"
              >
                <template v-if="s.kind === 'photo'">
                  <img :src="s.thumb_url || s.url" class="h-full w-full object-cover" />
                </template>
                <template v-else>
                  <div class="h-full w-full bg-black text-white flex items-center justify-center text-xs">Видео</div>
                </template>
              </button>
            </div>
          </Card>

          <!-- Documents -->
          <Card title="Документы">
            <ul class="space-y-2" v-if="auto.media.documents && auto.media.documents.length">
              <li v-for="d in auto.media.documents" :key="d.id" class="flex items-center justify-between rounded-md border p-2 text-sm">
                <span class="truncate mr-3">{{ d.file_name || d.name }}</span>
                <a :href="d.url" target="_blank" class="rounded-md border border-[var(--primary)] text-[var(--primary)] px-2 py-0.5 hover:bg-[var(--primary)] hover:text-white">Скачать</a>
              </li>
            </ul>
            <div v-else class="text-sm text-gray-500">Нет документов</div>
          </Card>

          <!-- Info -->
          <Card title="Информация">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
              <div>
                <div class="text-gray-500">VIN</div>
                <div class="font-medium">{{ auto.vin }}</div>
              </div>
              <div>
                <div class="text-gray-500">Бренд / Модель</div>
                <div class="font-medium">{{ auto.brand }} {{ auto.model }}</div>
              </div>
              <div>
                <div class="text-gray-500">Цвет</div>
                <div class="font-medium">{{ auto.color || '—' }}</div>
              </div>
              <div>
                <div class="text-gray-500">Год</div>
                <div class="font-medium">{{ auto.year || '—' }}</div>
              </div>
              <div>
                <div class="text-gray-500">Цена</div>
                <div class="font-medium">{{ auto.price ? new Intl.NumberFormat('ru-RU').format(auto.price) + ' ₽' : '—' }}</div>
              </div>
              <div>
                <div class="text-gray-500">Дата отправки</div>
                <div class="font-medium">{{ auto.departure_date || '—' }}</div>
              </div>
              <div>
                <div class="text-gray-500">Компания</div>
                <div class="font-medium">{{ auto.company?.name || '—' }}</div>
              </div>
              <div>
                <div class="text-gray-500">Отправитель</div>
                <div class="font-medium">{{ auto.sender?.name || '—' }}</div>
              </div>
              <div>
                <div class="text-gray-500">Перевозчик</div>
                <div class="font-medium">{{ auto.provider?.name || '—' }}</div>
              </div>
            </div>
          </Card>

          <!-- Current location -->
          <Card title="Текущая локация">
            <div v-if="auto.current_location" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
              <div>
                <div class="text-gray-500">Тип</div>
                <div class="font-medium">{{ auto.current_location.type_label }}</div>
              </div>
              <div>
                <div class="text-gray-500">Название</div>
                <div class="font-medium">{{ auto.current_location.name || '—' }}</div>
              </div>
              <div>
                <div class="text-gray-500">Статус</div>
                <div class="font-medium"><StatusBadge :status="auto.current_location.status" :label="auto.current_location.status_label" /></div>
              </div>
              <div>
                <div class="text-gray-500">Период</div>
                <div class="font-medium">{{ fmt(auto.current_location.started_at) }} — {{ fmt(auto.current_location.ended_at) || 'н.в.' }}</div>
              </div>
              <div class="sm:col-span-2">
                <div class="text-gray-500">Принял</div>
                <div class="font-medium">{{ auto.current_location.accepted_by?.name || '—' }}</div>
              </div>
              <div class="sm:col-span-2" v-if="auto.current_location.acceptance_note">
                <div class="text-gray-500">Примечание</div>
                <div class="font-medium">{{ auto.current_location.acceptance_note }}</div>
              </div>
            </div>
            <div v-else class="text-sm text-gray-500">Нет активной локации</div>
          </Card>

          <!-- History -->
          <Card title="История перемещений">
            <div v-if="auto.periods && auto.periods.length" class="divide-y">
              <div v-for="p in auto.periods" :key="p.id" class="py-3 grid grid-cols-1 sm:grid-cols-5 gap-2 text-sm">
                <div class="sm:col-span-1"><span class="text-gray-500">Тип</span><div class="font-medium">{{ p.type_label }}</div></div>
                <div class="sm:col-span-1"><span class="text-gray-500">Название</span><div class="font-medium">{{ p.name || '—' }}</div></div>
                <div class="sm:col-span-1"><span class="text-gray-500">Статус</span><div class="font-medium"><StatusBadge :status="p.status" :label="p.status_label" /></div></div>
                <div class="sm:col-span-1"><span class="text-gray-500">Период</span><div class="font-medium">{{ fmt(p.started_at) }} — {{ fmt(p.ended_at) || 'н.в.' }}</div></div>
                <div class="sm:col-span-1"><span class="text-gray-500">Принял</span><div class="font-medium">{{ p.accepted_by?.name || '—' }}</div></div>
                <div class="sm:col-span-5" v-if="p.acceptance_note"><span class="text-gray-500">Примечание</span><div class="font-medium">{{ p.acceptance_note }}</div></div>
              </div>
            </div>
            <div v-else class="text-sm text-gray-500">История отсутствует</div>
          </Card>
        </section>
      </div>

      <AutoTransitionsModal
        :open="transition.open"
        :action="transition.action"
        :form="form"
        :upload="upload"
        :customers="customers"
        :parkings="parkings"
        :availableParkings="availableParkings"
        :errors="form.errors"
        :processing="form.processing"
        @close="closeAll"
        @submit="submit"
        @drop-file="onDrop"
        @pick-file="onFilesSelected"
        @remove="removeFile"
      />

      <!-- Storage cost -->
      <Modal :open="modals.storage" title="Стоимость хранения" size="lg" @close="closeAll">
        <div class="space-y-4">
          <div class="text-sm text-gray-700">Всего дней: <span class="font-medium">{{ storage.total_days }}</span>, Итого: <span class="font-medium">{{ new Intl.NumberFormat('ru-RU').format(storage.total_cost) }}</span></div>
          <div v-for="p in storage.per_parkings" :key="p.parking.id" class="rounded-md border">
            <div class="px-3 py-2 border-b font-medium">Стоянка: {{ p.parking.name || ('#' + p.parking.id) }} — дней: {{ p.total_days }}, сумма: {{ new Intl.NumberFormat('ru-RU').format(p.total_cost) }}</div>
            <div class="max-h-64 overflow-auto">
              <table class="min-w-full text-sm">
                <thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left">Дата</th><th class="px-3 py-2 text-left">Цена</th></tr></thead>
                <tbody>
                  <tr v-for="d in p.days" :key="d.date">
                    <td class="px-3 py-1.5">{{ d.date }}</td>
                    <td class="px-3 py-1.5">{{ new Intl.NumberFormat('ru-RU').format(d.price) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <template #footer>
          <ClientButton variant="outline" @click="closeAll">Закрыть</ClientButton>
        </template>
      </Modal>
    </div>
  </ClientLayout>
</template>

<script setup>
import ClientLayout from '../../Layouts/ClientLayout.vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import { ref, computed, reactive } from 'vue'
import Card from '../../Components/Card.vue'
import StatusBadge from '../../Components/StatusBadge.vue'
import ClientButton from '../../Components/ClientButton.vue'
import Modal from '../../Components/Modal.vue'
import AutoTransitionsModal from '../../Components/Autos/AutoTransitionsModal.vue'
import AutoActionsPanel from '../../Components/Autos/AutoActionsPanel.vue'

const props = defineProps({
  auto: { type: Object, required: true },
  parkings: { type: Array, default: () => [] },
  customers: { type: Array, default: () => [] },
  actions: { type: Array, default: () => [] },
})

const slideIndex = ref(0)
const slides = computed(() => {
  const photos = (props.auto.media?.photos || []).map(p => ({ ...p, kind: 'photo' }))
  const videos = (props.auto.media?.videos || []).map(v => ({ ...v, kind: 'video' }))
  return [...photos, ...videos]
})
const currentSlide = computed(() => slides.value[slideIndex.value])

const next = () => { if (!slides.value.length) return; slideIndex.value = (slideIndex.value + 1) % slides.value.length }
const prev = () => { if (!slides.value.length) return; slideIndex.value = (slideIndex.value - 1 + slides.value.length) % slides.value.length }
const go = (i) => { if (!slides.value.length) return; slideIndex.value = i }

const fmt = (s) => { if (!s) return ''; const d = new Date(s); if (Number.isNaN(+d)) return s; return d.toLocaleString('ru-RU') }

// Modals state
const modals = reactive({ storage: false })
const transition = reactive({ open: false, action: '' })

// Shared upload helpers
const upload = reactive({ photos: [], videos: [], documents: [] })
const appendFiles = (kind, fileList) => { const arr = Array.from(fileList || []); upload[kind] = [...upload[kind], ...arr] }
const removeFile = (kind, idx) => { upload[kind].splice(idx, 1) }
const onDrop = (e, kind) => { e.preventDefault(); appendFiles(kind, e.dataTransfer.files) }
const onFilesSelected = (e, kind) => { appendFiles(kind, e.target.files); e.target.value = '' }

// Forms
const form = useForm({
  action: '',
  customer_id: null,
  arrival_date: new Date().toISOString().slice(0, 10),
  parking_id: null,
  sold_at: new Date().toISOString().slice(0, 10),
  note: '',
  photos: [], videos: [], documents: [],
})
const resetUploads = () => { upload.photos = []; upload.videos = []; upload.documents = [] }
const syncUploadsToForm = () => { form.photos = upload.photos; form.videos = upload.videos; form.documents = upload.documents }

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
    .post(`/autos/${props.auto.id}/transitions`, {
      preserveScroll: true,
      onSuccess: () => { closeAll(); router.reload({ preserveScroll: true }) },
    })
}

const closeAll = () => { modals.storage = false; transition.open = false; transition.action = ''; form.reset('action','customer_id','arrival_date','parking_id','sold_at','note'); resetUploads() }

const handleAction = (key) => {
  if (key === 'storage_cost') {
    openStorageCost()
    return
  }

  switch (key) {
    case 'move_to_customs':
      openMoveToCustoms();
      break
    case 'move_to_parking':
      openMoveToParking();
      break
    case 'accept_at_parking':
      openAcceptAtParking();
      break
    case 'move_to_customs_from_parking':
      openMoveToCustomsFromParking();
      break
    case 'move_to_other_parking':
      openMoveToOtherParking();
      break
    case 'sell':
      openSell();
      break
    case 'save_files':
      openSaveFiles();
      break
  }
}

// Openers
const openMoveToCustoms = () => { closeAll(); form.action = 'move_to_customs'; form.arrival_date = new Date().toISOString().slice(0,10); transition.action = 'move_to_customs'; transition.open = true }
const openMoveToParking = () => { closeAll(); form.action = 'move_to_parking'; transition.action = 'move_to_parking'; transition.open = true }
const openAcceptAtParking = () => { closeAll(); form.action = 'accept_at_parking'; transition.action = 'accept_at_parking'; transition.open = true }
const openMoveToCustomsFromParking = () => { closeAll(); form.action = 'move_to_customs_from_parking'; form.arrival_date = new Date().toISOString().slice(0,10); transition.action = 'move_to_customs_from_parking'; transition.open = true }
const openMoveToOtherParking = () => { closeAll(); form.action = 'move_to_other_parking'; transition.action = 'move_to_other_parking'; transition.open = true }
const openSell = () => { closeAll(); form.action = 'sell'; form.sold_at = new Date().toISOString().slice(0,10); transition.action = 'sell'; transition.open = true }
const openStorageCost = () => { closeAll(); modals.storage = true; loadStorage() }
const openSaveFiles = () => { closeAll(); form.action = 'save_files'; transition.action = 'save_files'; transition.open = true }

const currentParkingId = computed(() => props.auto.current_location && props.auto.current_location.type_label === 'Стоянка' ? props.auto.current_location.location_id : null)
const availableParkings = computed(() => props.parkings.filter(p => !currentParkingId.value || p.id !== currentParkingId.value))

// Storage cost
const storage = ref({ total_days: 0, total_cost: 0, per_parkings: [] })
const loadStorage = async () => {
  const res = await fetch(`/autos/${props.auto.id}/storage-cost`)
  if (res.ok) storage.value = await res.json()
}

</script>
