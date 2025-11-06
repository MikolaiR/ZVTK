<template>
  <AdminLayout>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <div class="flex flex-wrap gap-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Поиск</label>
          <input v-model="local.search" @keyup.enter="applyFilters" class="rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900" placeholder="Имя или email" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Статус</label>
          <select v-model="local.status" class="rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
            <option value="all">Все</option>
            <option value="active">Активные</option>
            <option value="inactive">Неактивные</option>
            <option value="deleted">Удалённые</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Роль</label>
          <select v-model="local.role" class="rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
            <option value="">Все</option>
            <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
          </select>
        </div>
        <div class="self-end">
          <button @click="applyFilters" class="rounded-md bg-gray-900 text-white px-3 py-2 text-sm">Применить</button>
        </div>
      </div>
      <div class="text-sm space-y-1">
        <div v-if="flash.success" class="text-green-600">{{ flash.success }}</div>
        <div v-if="flash.error" class="text-red-600">{{ flash.error }}</div>
        <div v-if="errors?.message" class="text-red-600">{{ errors.message }}</div>
      </div>
      <div class="self-end">
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
            <th class="px-4 py-2">Статус</th>
            <th class="px-4 py-2">Роли</th>
            <th class="px-4 py-2 text-right">Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in users.data" :key="u.id" :class="u.deleted_at ? 'opacity-60' : ''" class="border-t">
            <td class="px-4 py-2">{{ u.id }}</td>
            <td class="px-4 py-2">{{ u.name }}</td>
            <td class="px-4 py-2">{{ u.email }}</td>
            <td class="px-4 py-2">
              <span v-if="u.deleted_at" class="inline-flex items-center rounded bg-red-50 px-2 py-0.5 text-red-700">Удалён</span>
              <span v-else-if="u.is_active" class="inline-flex items-center rounded bg-green-50 px-2 py-0.5 text-green-700">Активен</span>
              <span v-else class="inline-flex items-center rounded bg-yellow-50 px-2 py-0.5 text-yellow-700">Выключен</span>
            </td>
            <td class="px-4 py-2">
              <div class="flex flex-wrap gap-2">
                <label v-for="r in roles" :key="`${u.id}-${r}`" class="inline-flex items-center gap-1">
                  <input type="checkbox" :checked="selectedRoles[u.id]?.has(r)" @change="toggleRole(u.id, r, $event.target.checked)" class="rounded border-gray-300 text-gray-900 focus:ring-gray-900" />
                  <span>{{ r }}</span>
                </label>
              </div>
              <div class="mt-2">
                <button @click="saveRoles(u.id)" :disabled="savingRoles[u.id]" class="rounded-md border px-2 py-1 text-xs hover:bg-gray-50 disabled:opacity-50">Сохранить</button>
              </div>
            </td>
            <td class="px-4 py-2 text-right">
              <div class="inline-flex items-center gap-2">
                <Link :href="`/admin/users/${u.id}/edit`" class="rounded-md border px-2 py-1 text-xs hover:bg-gray-50">Редактировать</Link>
                <button
                  v-if="!u.deleted_at"
                  @click="toggleActive(u.id)"
                  class="rounded-md border px-2 py-1 text-xs hover:bg-gray-50 disabled:opacity-50"
                  :disabled="meId === u.id"
                  :title="meId === u.id ? 'Нельзя менять собственный статус' : ''"
                >
                  {{ u.is_active ? 'Выключить' : 'Включить' }}
                </button>
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

      <div v-if="users.links?.length" class="flex justify-between items-center p-3 border-t text-sm">
        <div>Всего: {{ users.total }}</div>
        <div class="flex flex-wrap gap-1">
          <button
            v-for="l in users.links"
            :key="l.url + l.label"
            :disabled="!l.url"
            @click="visit(l.url)"
            class="px-3 py-1 rounded border hover:bg-gray-50 disabled:opacity-50"
            :class="l.active ? 'bg-gray-900 text-white hover:bg-gray-900' : ''"
          >
            <span v-html="l.label" />
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '../../../Layouts/AdminLayout.vue'
import { router, usePage, Link } from '@inertiajs/vue3'
import { reactive, onMounted, watch, computed } from 'vue'

const page = usePage()
const users = computed(() => page.props.users)
const roles = computed(() => page.props.roles)
const filters = computed(() => page.props.filters)
const flash = computed(() => page.props.flash)
const errors = computed(() => page.props.errors)

const local = reactive({
  search: filters.value?.search ?? '',
  status: filters.value?.status ?? 'all',
  role: filters.value?.role ?? '',
})

const selectedRoles = reactive({})
const savingRoles = reactive({})

// Current user id to prevent self-disable/delete
const meId = computed(() => page.props.auth?.user?.id ?? null)

const rebuildSelectedRoles = () => {
  // clear existing
  for (const key of Object.keys(selectedRoles)) delete selectedRoles[key]
  // init from current users list
  for (const u of users.value.data) {
    selectedRoles[u.id] = new Set(u.roles || [])
  }
}

onMounted(rebuildSelectedRoles)
watch(users, rebuildSelectedRoles)

const applyFilters = () => {
  router.get('/admin/users', { search: local.search, status: local.status, role: local.role }, { preserveScroll: true })
}

const visit = (url) => {
  router.get(url, {}, { preserveScroll: true })
}

const toggleRole = (userId, role, checked) => {
  const set = selectedRoles[userId] || new Set()
  if (checked) set.add(role)
  else set.delete(role)
  selectedRoles[userId] = set
}

const saveRoles = (userId) => {
  savingRoles[userId] = true
  router.post(`/admin/users/${userId}/roles`, { roles: Array.from(selectedRoles[userId] || []) }, {
    preserveScroll: true,
    onFinish: () => { savingRoles[userId] = false },
  })
}

const toggleActive = (userId) => {
  router.post(`/admin/users/${userId}/toggle-active`, {}, { preserveScroll: true })
}

const remove = (userId) => {
  router.delete(`/admin/users/${userId}`, { preserveScroll: true })
}

const restore = (userId) => {
  router.post(`/admin/users/${userId}/restore`, {}, { preserveScroll: true })
}
</script>
