<template>
  <AdminLayout>
    <div class="mb-6 flex items-end justify-between">
      <h2 class="text-lg font-semibold">Пермишены</h2>
      <div class="flex items-center gap-2">
        <Link href="/admin/roles/create" class="text-sm rounded-md border px-3 py-2 hover:bg-gray-50">Добавить роль</Link>
        <Link href="/admin/permissions/create" class="inline-flex items-center rounded-md bg-gray-900 text-white px-3 py-2 text-sm hover:bg-black">Создать пермишен</Link>
      </div>
    </div>

    <div class="overflow-x-auto rounded-lg border bg-white">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-left">
          <tr>
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Название</th>
            <th class="px-4 py-2">Роли</th>
            <th class="px-4 py-2 text-right">Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in permissions.data" :key="p.id" class="border-t">
            <td class="px-4 py-2">{{ p.id }}</td>
            <td class="px-4 py-2">{{ p.name }}</td>
            <td class="px-4 py-2">
              <div class="flex flex-wrap gap-1">
                <Badge v-for="r in p.roles" :key="`${p.id}-r-${r}`">{{ r }}</Badge>
              </div>
            </td>
            <td class="px-4 py-2 text-right">
              <div class="inline-flex items-center gap-2">
                <Link :href="`/admin/permissions/${p.id}/edit`" class="rounded-md border px-2 py-1 text-xs hover:bg-gray-50">Редактировать</Link>
                <button @click="remove(p.id)" class="rounded-md border px-2 py-1 text-xs hover:bg-red-50 text-red-700">Удалить</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <Pagination :links="permissions.links" :total="permissions.total" />
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
const permissions = computed(() => page.props.permissions)

const remove = (id) => {
  router.delete(`/admin/permissions/${id}`, { preserveScroll: true })
}
</script>
