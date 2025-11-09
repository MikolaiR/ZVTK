<template>
  <ClientLayout>
    <div>
      <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold">{{ auto.title || 'Автомобиль' }}</h1>
        <Link href="/autos" class="text-sm rounded-md border px-3 py-1.5 hover:bg-gray-100">К списку</Link>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Actions (mobile first) -->
        <aside class="order-1 md:order-2 md:col-span-1">
          <div class="sticky top-20 space-y-3">
            <div class="rounded-lg border bg-white p-4">
              <div class="text-sm text-gray-600 mb-2">Статус</div>
              <div class="text-lg font-semibold">{{ auto.status_label }}</div>
            </div>

            <div class="rounded-lg border bg-white p-4">
              <div class="font-medium mb-2">Действия</div>
              <div class="space-y-2">
                <template v-if="isDelivery">
                  <button @click="openMoveToCustoms" class="w-full rounded-md bg-gray-900 px-4 py-2 text-white hover:bg-black">Переместить на таможню</button>
                </template>
                <template v-else-if="isCustomer">
                  <button @click="openMoveToParking" class="w-full rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Переместить на стоянку</button>
                  <button @click="openSell" class="w-full rounded-md bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">Передана владельцу</button>
                </template>
                <template v-else-if="isDeliveryToParking">
                  <button @click="openAcceptAtParking" class="w-full rounded-md bg-gray-900 px-4 py-2 text-white hover:bg-black">Принять на стоянку</button>
                </template>
                <template v-else-if="isParking">
                  <button @click="openSell" class="w-full rounded-md bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">Передана владельцу</button>
                  <button @click="openMoveToCustomsFromParking" class="w-full rounded-md bg-gray-900 px-4 py-2 text-white hover:bg-black">Переместить на таможню</button>
                  <button @click="openMoveToOtherParking" class="w-full rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Переместить на другую стоянку</button>
                  <button @click="openStorageCost" class="w-full rounded-md border px-4 py-2 hover:bg-gray-50">Рассчитать стоимость хранения</button>
                </template>
                <template v-else-if="isSale">
                  <button @click="openStorageCost" class="w-full rounded-md border px-4 py-2 hover:bg-gray-50">Стоимость хранения</button>
                </template>
                <button class="w-full rounded-md border px-4 py-2 hover:bg-gray-50">Скачать документы ZIP</button>
              </div>
            </div>
          </div>
        </aside>

        <!-- Main content -->
        <section class="order-2 md:order-1 md:col-span-2 space-y-6">
          <!-- Media slider -->
          <div class="rounded-lg border bg-white p-4">
            <div class="flex items-center justify-between mb-3">
              <h2 class="text-base font-medium">Медиа</h2>
              <div class="text-sm text-gray-500" v-if="slides.length">{{ slideIndex + 1 }} / {{ slides.length }}</div>
            </div>
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
                  i === slideIndex ? 'ring-2 ring-gray-900' : ''
                ]"
              >
                <template v-if="s.kind === 'photo'">
                  <img :src="s.url" class="h-full w-full object-cover" />
                </template>
                <template v-else>
                  <div class="h-full w-full bg-black text-white flex items-center justify-center text-xs">Видео</div>
                </template>
              </button>
            </div>
          </div>

          <!-- Documents -->
          <div class="rounded-lg border bg-white p-4">
            <h2 class="text-base font-medium mb-3">Документы</h2>
            <ul class="space-y-2" v-if="auto.media.documents && auto.media.documents.length">
              <li v-for="d in auto.media.documents" :key="d.id" class="flex items-center justify-between rounded-md border p-2 text-sm">
                <span class="truncate mr-3">{{ d.file_name || d.name }}</span>
                <a :href="d.url" target="_blank" class="rounded-md border px-2 py-0.5 hover:bg-gray-50">Скачать</a>
              </li>
            </ul>
            <div v-else class="text-sm text-gray-500">Нет документов</div>
          </div>

          <!-- Info -->
          <div class="rounded-lg border bg-white p-4">
            <h2 class="text-base font-medium mb-3">Информация</h2>
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
          </div>

          <!-- Current location -->
          <div class="rounded-lg border bg-white p-4">
            <h2 class="text-base font-medium mb-3">Текущая локация</h2>
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
                <div class="font-medium">{{ auto.current_location.status_label }}</div>
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
          </div>

          <!-- History -->
          <div class="rounded-lg border bg-white p-4">
            <h2 class="text-base font-medium mb-3">История перемещений</h2>
            <div v-if="auto.periods && auto.periods.length" class="divide-y">
              <div v-for="p in auto.periods" :key="p.id" class="py-3 grid grid-cols-1 sm:grid-cols-5 gap-2 text-sm">
                <div class="sm:col-span-1"><span class="text-gray-500">Тип</span><div class="font-medium">{{ p.type_label }}</div></div>
                <div class="sm:col-span-1"><span class="text-gray-500">Название</span><div class="font-medium">{{ p.name || '—' }}</div></div>
                <div class="sm:col-span-1"><span class="text-gray-500">Статус</span><div class="font-medium">{{ p.status_label }}</div></div>
                <div class="sm:col-span-1"><span class="text-gray-500">Период</span><div class="font-medium">{{ fmt(p.started_at) }} — {{ fmt(p.ended_at) || 'н.в.' }}</div></div>
                <div class="sm:col-span-1"><span class="text-gray-500">Принял</span><div class="font-medium">{{ p.accepted_by?.name || '—' }}</div></div>
                <div class="sm:col-span-5" v-if="p.acceptance_note"><span class="text-gray-500">Примечание</span><div class="font-medium">{{ p.acceptance_note }}</div></div>
              </div>
            </div>
            <div v-else class="text-sm text-gray-500">История отсутствует</div>
          </div>
        </section>
      </div>

      <!-- Modals overlay -->
      <div v-if="Object.values(modals).some(Boolean)" class="fixed inset-0 z-40 bg-black/30"></div>

      <!-- Move to Customs -->
      <div v-if="modals.moveToCustoms" class="fixed inset-0 z-50 grid place-items-center p-4">
        <div class="w-full max-w-xl rounded-lg border bg-white p-4">
          <div class="flex items-center justify-between mb-3"><h3 class="text-lg font-semibold">Переместить на таможню</h3><button @click="closeAll">✕</button></div>
          <div class="space-y-4">
            <div>
              <label class="block text-sm mb-1">Таможня</label>
              <select v-model="form.customer_id" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                <option :value="null" disabled>Выберите таможню</option>
                <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
              <p v-if="form.errors.customer_id" class="text-sm text-red-600 mt-1">{{ form.errors.customer_id }}</p>
            </div>
            <div>
              <label class="block text-sm mb-1">Дата прибытия</label>
              <input type="date" v-model="form.arrival_date" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900" />
              <p v-if="form.errors.arrival_date" class="text-sm text-red-600 mt-1">{{ form.errors.arrival_date }}</p>
            </div>
            <Uploads :upload="upload" :errors="form.errors" @drop-file="onDrop" @pick-file="onFilesSelected" @remove="removeFile" />
            <div>
              <label class="block text-sm mb-1">Комментарий</label>
              <textarea v-model="form.note" rows="3" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900"></textarea>
            </div>
          </div>
          <div class="mt-4 flex items-center justify-end gap-2">
            <button class="rounded-md border px-3 py-1.5" @click="closeAll">Отмена</button>
            <button class="rounded-md bg-gray-900 px-4 py-2 text-white disabled:opacity-50" :disabled="form.processing || !form.customer_id" @click="submit">Подтвердить</button>
          </div>
        </div>
      </div>

      <!-- Move to Parking -->
      <div v-if="modals.moveToParking" class="fixed inset-0 z-50 grid place-items-center p-4">
        <div class="w-full max-w-xl rounded-lg border bg-white p-4">
          <div class="flex items-center justify-between mb-3"><h3 class="text-lg font-semibold">Переместить на стоянку</h3><button @click="closeAll">✕</button></div>
          <div class="space-y-4">
            <div>
              <label class="block text-sm mb-1">Стоянка</label>
              <select v-model="form.parking_id" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                <option :value="null" disabled>Выберите стоянку</option>
                <option v-for="p in parkings" :key="p.id" :value="p.id">{{ p.name }}</option>
              </select>
              <p v-if="form.errors.parking_id" class="text-sm text-red-600 mt-1">{{ form.errors.parking_id }}</p>
            </div>
            <Uploads :upload="upload" :errors="form.errors" @drop-file="onDrop" @pick-file="onFilesSelected" @remove="removeFile" />
            <div>
              <label class="block text-sm mb-1">Комментарий</label>
              <textarea v-model="form.note" rows="3" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900"></textarea>
            </div>
          </div>
          <div class="mt-4 flex items-center justify-end gap-2">
            <button class="rounded-md border px-3 py-1.5" @click="closeAll">Отмена</button>
            <button class="rounded-md bg-blue-600 px-4 py-2 text-white disabled:opacity-50" :disabled="form.processing || !form.parking_id" @click="submit">Подтвердить</button>
          </div>
        </div>
      </div>

      <!-- Accept at Parking -->
      <div v-if="modals.acceptAtParking" class="fixed inset-0 z-50 grid place-items-center p-4">
        <div class="w-full max-w-xl rounded-lg border bg-white p-4">
          <div class="flex items-center justify-between mb-3"><h3 class="text-lg font-semibold">Принять на стоянку</h3><button @click="closeAll">✕</button></div>
          <div class="space-y-4">
            <Uploads :upload="upload" :errors="form.errors" @drop-file="onDrop" @pick-file="onFilesSelected" @remove="removeFile" />
            <div>
              <label class="block text-sm mb-1">Комментарий</label>
              <textarea v-model="form.note" rows="3" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900"></textarea>
            </div>
          </div>
          <div class="mt-4 flex items-center justify-end gap-2">
            <button class="rounded-md border px-3 py-1.5" @click="closeAll">Отмена</button>
            <button class="rounded-md bg-gray-900 px-4 py-2 text-white disabled:opacity-50" :disabled="form.processing" @click="submit">Подтвердить</button>
          </div>
        </div>
      </div>

      <!-- Move to Customs from Parking -->
      <div v-if="modals.moveToCustomsFromParking" class="fixed inset-0 z-50 grid place-items-center p-4">
        <div class="w-full max-w-xl rounded-lg border bg-white p-4">
          <div class="flex items-center justify-between mb-3"><h3 class="text-lg font-semibold">Переместить на таможню</h3><button @click="closeAll">✕</button></div>
          <div class="space-y-4">
            <div>
              <label class="block text-sm mb-1">Таможня</label>
              <select v-model="form.customer_id" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                <option :value="null" disabled>Выберите таможню</option>
                <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
              <p v-if="form.errors.customer_id" class="text-sm text-red-600 mt-1">{{ form.errors.customer_id }}</p>
            </div>
            <div>
              <label class="block text-sm mb-1">Дата прибытия</label>
              <input type="date" v-model="form.arrival_date" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900" />
              <p v-if="form.errors.arrival_date" class="text-sm text-red-600 mt-1">{{ form.errors.arrival_date }}</p>
            </div>
            <Uploads :upload="upload" :errors="form.errors" @drop-file="onDrop" @pick-file="onFilesSelected" @remove="removeFile" />
            <div>
              <label class="block text-sm mb-1">Комментарий</label>
              <textarea v-model="form.note" rows="3" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900"></textarea>
            </div>
          </div>
          <div class="mt-4 flex items-center justify-end gap-2">
            <button class="rounded-md border px-3 py-1.5" @click="closeAll">Отмена</button>
            <button class="rounded-md bg-gray-900 px-4 py-2 text-white disabled:opacity-50" :disabled="form.processing || !form.customer_id" @click="submit">Подтвердить</button>
          </div>
        </div>
      </div>

      <!-- Move to Other Parking -->
      <div v-if="modals.moveToOtherParking" class="fixed inset-0 z-50 grid place-items-center p-4">
        <div class="w-full max-w-xl rounded-lg border bg-white p-4">
          <div class="flex items-center justify-between mb-3"><h3 class="text-lg font-semibold">Переместить на другую стоянку</h3><button @click="closeAll">✕</button></div>
          <div class="space-y-4">
            <div>
              <label class="block text-sm mb-1">Стоянка</label>
              <select v-model="form.parking_id" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                <option :value="null" disabled>Выберите стоянку</option>
                <option v-for="p in availableParkings" :key="p.id" :value="p.id">{{ p.name }}</option>
              </select>
              <p v-if="form.errors.parking_id" class="text-sm text-red-600 mt-1">{{ form.errors.parking_id }}</p>
            </div>
            <Uploads :upload="upload" :errors="form.errors" @drop-file="onDrop" @pick-file="onFilesSelected" @remove="removeFile" />
            <div>
              <label class="block text-sm mb-1">Комментарий</label>
              <textarea v-model="form.note" rows="3" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900"></textarea>
            </div>
          </div>
          <div class="mt-4 flex items-center justify-end gap-2">
            <button class="rounded-md border px-3 py-1.5" @click="closeAll">Отмена</button>
            <button class="rounded-md bg-blue-600 px-4 py-2 text-white disabled:opacity-50" :disabled="form.processing || !form.parking_id" @click="submit">Подтвердить</button>
          </div>
        </div>
      </div>

      <!-- Sell -->
      <div v-if="modals.sell" class="fixed inset-0 z-50 grid place-items-center p-4">
        <div class="w-full max-w-xl rounded-lg border bg-white p-4">
          <div class="flex items-center justify-between mb-3"><h3 class="text-lg font-semibold">Передана владельцу</h3><button @click="closeAll">✕</button></div>
          <div class="space-y-4">
            <div>
              <label class="block text-sm mb-1">Дата передачи</label>
              <input type="date" v-model="form.sold_at" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900" />
              <p v-if="form.errors.sold_at" class="text-sm text-red-600 mt-1">{{ form.errors.sold_at }}</p>
            </div>
            <Uploads :upload="upload" :errors="form.errors" @drop-file="onDrop" @pick-file="onFilesSelected" @remove="removeFile" />
            <div>
              <label class="block text-sm mb-1">Комментарий</label>
              <textarea v-model="form.note" rows="3" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900"></textarea>
            </div>
          </div>
          <div class="mt-4 flex items-center justify-end gap-2">
            <button class="rounded-md border px-3 py-1.5" @click="closeAll">Отмена</button>
            <button class="rounded-md bg-emerald-600 px-4 py-2 text-white disabled:opacity-50" :disabled="form.processing" @click="submit">Подтвердить</button>
          </div>
        </div>
      </div>

      <!-- Storage cost -->
      <div v-if="modals.storage" class="fixed inset-0 z-50 grid place-items-center p-4">
        <div class="w-full max-w-3xl rounded-lg border bg-white p-4">
          <div class="flex items-center justify-between mb-3"><h3 class="text-lg font-semibold">Стоимость хранения</h3><button @click="closeAll">✕</button></div>
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
          <div class="mt-4 flex items-center justify-end">
            <button class="rounded-md border px-3 py-1.5" @click="closeAll">Закрыть</button>
          </div>
        </div>
      </div>
    </div>
  </ClientLayout>
</template>

<script setup>
import ClientLayout from '../../Layouts/ClientLayout.vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import { ref, computed, reactive } from 'vue'
import Uploads from '../../Components/Uploads.vue'

const props = defineProps({
  auto: { type: Object, required: true },
  parkings: { type: Array, default: () => [] },
  customers: { type: Array, default: () => [] },
})

const Statuses = { Delivery: 1, Customer: 2, DeliveryToParking: 3, Parking: 4, Sale: 5 }
const isDelivery = computed(() => props.auto.status == Statuses.Delivery)
const isCustomer = computed(() => props.auto.status == Statuses.Customer)
const isDeliveryToParking = computed(() => props.auto.status == Statuses.DeliveryToParking)
const isParking = computed(() => props.auto.status == Statuses.Parking)
const isSale = computed(() => props.auto.status == Statuses.Sale)

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
const modals = reactive({
  moveToCustoms: false,
  moveToParking: false,
  acceptAtParking: false,
  moveToCustomsFromParking: false,
  moveToOtherParking: false,
  sell: false,
  storage: false,
})

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
  form.post(`/autos/${props.auto.id}/transitions`, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => { closeAll(); router.reload({ preserveScroll: true }) },
  })
}

const closeAll = () => { Object.keys(modals).forEach(k => modals[k] = false); form.reset('action','customer_id','arrival_date','parking_id','sold_at','note'); resetUploads() }

// Openers
const openMoveToCustoms = () => { closeAll(); form.action = 'move_to_customs'; form.arrival_date = new Date().toISOString().slice(0,10); modals.moveToCustoms = true }
const openMoveToParking = () => { closeAll(); form.action = 'move_to_parking'; modals.moveToParking = true }
const openAcceptAtParking = () => { closeAll(); form.action = 'accept_at_parking'; modals.acceptAtParking = true }
const openMoveToCustomsFromParking = () => { closeAll(); form.action = 'move_to_customs_from_parking'; form.arrival_date = new Date().toISOString().slice(0,10); modals.moveToCustomsFromParking = true }
const openMoveToOtherParking = () => { closeAll(); form.action = 'move_to_other_parking'; modals.moveToOtherParking = true }
const openSell = () => { closeAll(); form.action = 'sell'; form.sold_at = new Date().toISOString().slice(0,10); modals.sell = true }
const openStorageCost = () => { closeAll(); modals.storage = true; loadStorage() }

const currentParkingId = computed(() => props.auto.current_location && props.auto.current_location.type_label === 'Стоянка' ? props.auto.current_location.location_id : null)
const availableParkings = computed(() => props.parkings.filter(p => !currentParkingId.value || p.id !== currentParkingId.value))

// Storage cost
const storage = ref({ total_days: 0, total_cost: 0, per_parkings: [] })
const loadStorage = async () => {
  const res = await fetch(`/autos/${props.auto.id}/storage-cost`)
  if (res.ok) storage.value = await res.json()
}

</script>
