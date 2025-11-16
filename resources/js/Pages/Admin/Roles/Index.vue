<template>
  <AdminLayout>
    <div class="mb-6 flex items-end justify-between">
      <h2 class="text-lg font-semibold">Роли</h2>
      <div class="flex items-center gap-2">
        <Link href="/admin/permissions/create" class="text-sm rounded-md border px-3 py-2 hover:bg-gray-50">Добавить пермишен</Link>
        <Link href="/admin/roles/create" class="inline-flex items-center rounded-md bg-gray-900 text-white px-3 py-2 text-sm hover:bg-black">Создать роль</Link>
      </div>
    </div>

    <div class="overflow-x-auto rounded-lg border bg-white">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-left">
          <tr>
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Название</th>
            <th class="px-4 py-2">Пермишены</th>
            <th class="px-4 py-2 text-right">Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in roles.data" :key="r.id" class="border-t">
            <td class="px-4 py-2">{{ r.id }}</td>
            <td class="px-4 py-2">{{ r.name }}</td>
            <td class="px-4 py-2">
              <div class="flex flex-wrap gap-1">
                <Badge v-for="p in r.permissions" :key="`${r.id}-p-${p}`" variant="secondary">{{ p }}</Badge>
              </div>
            </td>
            <td class="px-4 py-2 text-right">
              <div class="inline-flex items-center gap-2">
                <Link :href="`/admin/roles/${r.id}/edit`" class="rounded-md border px-2 py-1 text-xs hover:bg-gray-50">Редактировать</Link>
                <button @click="remove(r.id)" class="rounded-md border px-2 py-1 text-xs hover:bg-red-50 text-red-700">Удалить</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <Pagination :links="roles.links" :total="roles.total" />
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '../../../Layouts/AdminLayout.vue'
import { router, usePage, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import Pagination from '../../../Components/Pagination.vue'
import Badge from '../../../Components/Badge.vue'

const page = usePage()
const roles = computed(() => page.props.roles)

const remove = (id) => {
  router.delete(`/admin/roles/${id}`, { preserveScroll: true })
}
</script>
