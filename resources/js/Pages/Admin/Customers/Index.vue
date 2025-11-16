<template>
  <AdminLayout>
    <div class="max-w-5xl">
      <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Таможни</h1>
        <Link href="/admin/customers/create" class="text-sm rounded-md border px-3 py-1.5 hover:bg-gray-100">Добавить</Link>
      </div>

      <div class="overflow-x-auto rounded-lg border bg-white">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-left">
            <tr>
              <th class="px-3 py-2">Название</th>
              <th class="px-3 py-2">Телефон</th>
              <th class="px-3 py-2">Email</th>
              <th class="px-3 py-2">Документ</th>
              <th class="px-3 py-2 text-right">Действия</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in customers.data" :key="c.id" class="border-t">
              <td class="px-3 py-2">{{ c.name }}</td>
              <td class="px-3 py-2">{{ c.phone || '—' }}</td>
              <td class="px-3 py-2">{{ c.email || '—' }}</td>
              <td class="px-3 py-2">{{ c.document_number || '—' }}</td>
              <td class="px-3 py-2 text-right">
                <div class="inline-flex items-center gap-2">
                  <Link :href="`/admin/customers/${c.id}/edit`" class="rounded-md border px-2 py-1 text-xs hover:bg-gray-100">Редактировать</Link>
                  <button class="rounded-md border px-2 py-1 text-xs hover:bg-red-50 border-red-300 text-red-700" @click="destroy(c)">Удалить</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        <Pagination :links="customers.links" />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '../../../Layouts/AdminLayout.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import Pagination from '../../../Components/Pagination.vue'

const page = usePage()
const customers = page.props.customers

const destroy = (c) => {
  if (!confirm('Удалить таможню?')) return
  router.delete(`/admin/customers/${c.id}`)
}
</script>
