<template>
  <AdminLayout>
    <div class="max-w-5xl">
      <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Стоянки</h1>
        <Link href="/admin/parkings/create" class="text-sm rounded-md border px-3 py-1.5 hover:bg-gray-100">Добавить</Link>
      </div>

      <div class="overflow-x-auto rounded-lg border bg-white">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-left">
            <tr>
              <th class="px-3 py-2">Название</th>
              <th class="px-3 py-2">Адрес</th>
              <th class="px-3 py-2">Компания</th>
              <th class="px-3 py-2 text-right">Действия</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in parkings.data" :key="p.id" class="border-t">
              <td class="px-3 py-2">{{ p.name || '—' }}</td>
              <td class="px-3 py-2">{{ p.address }}</td>
              <td class="px-3 py-2">{{ p.company?.name || '—' }}</td>
              <td class="px-3 py-2 text-right">
                <div class="inline-flex items-center gap-2">
                  <Link :href="`/admin/parkings/${p.id}/edit`" class="rounded-md border px-2 py-1 text-xs hover:bg-gray-100">Редактировать</Link>
                  <button class="rounded-md border px-2 py-1 text-xs hover:bg-red-50 border-red-300 text-red-700" @click="destroy(p)">Удалить</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        <Pagination :links="parkings.links" />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '../../../Layouts/AdminLayout.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import Pagination from '../../../Components/Pagination.vue'

const page = usePage()
const parkings = page.props.parkings

const destroy = (p) => {
  if (!confirm('Удалить стоянку?')) return
  router.delete(`/admin/parkings/${p.id}`)
}
</script>
