<template>
  <AdminLayout>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <div class="flex flex-wrap items-end gap-3">
        <FormInput label="Поиск" v-model="local.search" placeholder="Название или VIN" />
        <div class="self-end">
          <Link href="/autos/create" class="inline-flex items-center rounded-md bg-gray-900 text-white px-3 py-2 text-sm hover:bg-black">Добавить авто</Link>
        </div>
      </div>
    </div>

    <div class="overflow-x-auto rounded-lg border bg-white">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-left">
          <tr>
            <th class="px-4 py-2 w-28">Фото</th>
            <th class="px-4 py-2">Заголовок</th>
            <th class="px-4 py-2">Статус</th>
            <th class="px-4 py-2 text-right">Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in autos.data" :key="a.id" class="border-t">
            <td class="px-4 py-2">
              <img :src="a.preview_url || '/images/not_photo.png'" class="h-16 w-24 object-cover rounded border" />
            </td>
            <td class="px-4 py-2">
              <div class="font-medium">{{ a.title }}</div>
              <div class="text-xs text-gray-500">VIN: {{ a.vin }}</div>
            </td>
            <td class="px-4 py-2">
              <span class="inline-flex items-center rounded bg-gray-100 px-2 py-0.5">{{ a.status_label }}</span>
            </td>
            <td class="px-4 py-2 text-right">
              <div class="inline-flex items-center gap-2">
                <Link :href="`/admin/autos/${a.id}/edit`" class="rounded-md border px-2 py-1 text-xs hover:bg-gray-50">Редактировать</Link>
                <button @click="remove(a.id)" class="rounded-md border px-2 py-1 text-xs hover:bg-red-50 text-red-700">Удалить</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <Pagination :links="autos.links" :total="autos.total" />
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '../../../Layouts/AdminLayout.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, reactive, watch } from 'vue'
import FormInput from '../../../Components/FormInput.vue'
import Pagination from '../../../Components/Pagination.vue'

const page = usePage()
const autos = computed(() => page.props.autos)
const filters = computed(() => page.props.filters)

const local = reactive({ search: filters.value?.search ?? '' })

let t = null
watch(() => local.search, (v) => {
  if (t) clearTimeout(t)
  t = setTimeout(() => {
    router.get('/admin/autos', { search: v }, { replace: true, preserveScroll: true, preserveState: true, only: ['autos', 'filters'] })
  }, 300)
})

const remove = (id) => {
  router.delete(`/admin/autos/${id}`, { preserveScroll: true })
}
</script>
