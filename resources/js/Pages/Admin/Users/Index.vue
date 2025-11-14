<template>
  <AdminLayout>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <div class="flex flex-wrap gap-3">
        <FormInput label="Поиск" v-model="local.search" placeholder="Имя или email" @keyup.enter="applyFilters" />
        <div class="w-40">
          <FormSelect label="Статус" v-model="local.status" :options="statusOptions" />
        </div>
        <div class="w-48">
          <FormSelect label="Роль" v-model="local.role" :options="roleOptions" placeholder="Все" />
        </div>
        <div class="self-end">
          <AppButton variant="primary" @click="applyFilters">Применить</AppButton>
        </div>
      </div>
      <FlashMessages :flash="flash" :errors="errors" />
      <div class="self-end flex items-center gap-2">
        <Link href="/admin/roles/create" class="text-sm rounded-md border px-3 py-2 hover:bg-gray-50">Добавить роль</Link>
        <Link href="/admin/permissions/create" class="text-sm rounded-md border px-3 py-2 hover:bg-gray-50">Добавить пермишен</Link>
        <Link href="/admin/users/create" class="inline-flex items-center rounded-md bg-gray-900 text-white px-3 py-2 text-sm hover:bg-black">Создать пользователя</Link>
      </div>
    </div>

    <div class="overflow-x-auto rounded-lg border bg-white">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-left">
          <tr>
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Имя</th>
            <th class="px-4 py-2">Email</th>
            <th class="px-4 py-2">Компания</th>
            <th class="px-4 py-2">Статус</th>
            <th class="px-4 py-2">Роли</th>
            <th class="px-4 py-2">Пермишены</th>
            <th class="px-4 py-2 text-right">Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in users.data" :key="u.id" :class="u.deleted_at ? 'opacity-60' : ''" class="border-t">
            <td class="px-4 py-2">{{ u.id }}</td>
            <td class="px-4 py-2">{{ u.name }}</td>
            <td class="px-4 py-2">{{ u.email }}</td>
            <td class="px-4 py-2">{{ u.company?.name || '—' }}</td>
            <td class="px-4 py-2">
              <span v-if="u.deleted_at" class="inline-flex items-center rounded bg-red-50 px-2 py-0.5 text-red-700">Удалён</span>
              <span v-else-if="u.is_active" class="inline-flex items-center rounded bg-green-50 px-2 py-0.5 text-green-700">Активен</span>
              <span v-else class="inline-flex items-center rounded bg-yellow-50 px-2 py-0.5 text-yellow-700">Выключен</span>
            </td>
            <td class="px-4 py-2">
              <div class="flex flex-wrap gap-1">
                <Badge v-for="r in u.roles" :key="`${u.id}-r-${r}`">{{ r }}</Badge>
              </div>
            </td>
            <td class="px-4 py-2">
              <div class="flex flex-wrap gap-1">
                <Badge v-for="p in u.permissions" :key="`${u.id}-p-${p}`" variant="secondary">{{ p }}</Badge>
              </div>
            </td>
            <td class="px-4 py-2 text-right">
              <div class="inline-flex items-center gap-2">
                <Link :href="`/admin/users/${u.id}/edit`" class="rounded-md border px-2 py-1 text-xs hover:bg-gray-50">Редактировать</Link>
                <button
                  v-if="!u.deleted_at"
                  @click="remove(u.id)"
                  class="rounded-md border px-2 py-1 text-xs hover:bg-red-50 text-red-700 disabled:opacity-50"
                  :disabled="meId === u.id"
                  :title="meId === u.id ? 'Нельзя удалять свой аккаунт' : ''"
                >
                  Удалить
                </button>
                <button v-else @click="restore(u.id)" class="rounded-md border px-2 py-1 text-xs hover:bg-gray-50">Восстановить</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <Pagination :links="users.links" :total="users.total" />
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '../../../Layouts/AdminLayout.vue'
import { router, usePage, Link } from '@inertiajs/vue3'
import { reactive, computed } from 'vue'
import FormInput from '../../../Components/FormInput.vue'
import FormSelect from '../../../Components/FormSelect.vue'
import AppButton from '../../../Components/AppButton.vue'
import FlashMessages from '../../../Components/FlashMessages.vue'
import Pagination from '../../../Components/Pagination.vue'
import Badge from '../../../Components/Badge.vue'

const page = usePage()
const users = computed(() => page.props.users)
const roles = computed(() => page.props.roles)
const filters = computed(() => page.props.filters)
const flash = computed(() => page.props.flash)
const errors = computed(() => page.props.errors)
const meId = computed(() => page.props.auth?.user?.id ?? null)

const local = reactive({
  search: filters.value?.search ?? '',
  status: filters.value?.status ?? 'all',
  role: filters.value?.role ?? '',
})

const applyFilters = () => {
  router.get('/admin/users', { search: local.search, status: local.status, role: local.role }, { preserveScroll: true })
}

const remove = (userId) => {
  router.delete(`/admin/users/${userId}`, { preserveScroll: true })
}

const restore = (userId) => {
  router.post(`/admin/users/${userId}/restore`, {}, { preserveScroll: true })
}

const statusOptions = [
  { label: 'Все', value: 'all' },
  { label: 'Активные', value: 'active' },
  { label: 'Неактивные', value: 'inactive' },
  { label: 'Удалённые', value: 'deleted' },
]

const roleOptions = [{ label: 'Все', value: '' }, ...roles.value.map(r => ({ label: r, value: r }))]
</script>
