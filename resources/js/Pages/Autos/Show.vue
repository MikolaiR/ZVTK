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
                <template v-if="onCustoms">
                  <button class="w-full rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Отправить на стоянку</button>
                  <button class="w-full rounded-md bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">Отправить покупателю</button>
                </template>
                <template v-else>
                  <button class="w-full rounded-md bg-gray-900 px-4 py-2 text-white hover:bg-black">Принять на таможню</button>
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
    </div>
  </ClientLayout>
</template>

<script setup>
import ClientLayout from '../../Layouts/ClientLayout.vue'
import { Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  auto: { type: Object, required: true },
})

const onCustoms = computed(() => props.auto.current_location && props.auto.current_location.type_label === 'Таможня')

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
</script>
