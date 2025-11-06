<template>
  <AdminLayout>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <div class="flex flex-wrap gap-3 items-end">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Поиск</label>
          <input v-model="local.search" @keyup.enter="applyFilters" class="rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900" placeholder="Наименование цвета" />
        </div>
        <label class="inline-flex items-center gap-2 mb-1">
          <input type="checkbox" v-model="local.show_deleted" class="rounded border-gray-300 text-gray-900 focus:ring-gray-900" />
          <span class="text-sm text-gray-700">Показывать удалённые</span>
        </label>
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
        <Link href="/admin/colors/create" class="inline-flex items-center rounded-md bg-gray-900 text-white px-3 py-2 text-sm hover:bg-black">Создать цвет</Link>
      </div>
    </div>

    <div class="overflow-x-auto rounded-lg border bg-white">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-left">
          <tr>
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Наименование</th>
            <th class="px-4 py-2">Статус</th>
            <th class="px-4 py-2 text-right">Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in colors.data" :key="c.id" :class="c.deleted_at ? 'opacity-60' : ''" class="border-t">
            <td class="px-4 py-2">{{ c.id }}</td>
            <td class="px-4 py-2">{{ c.name }}</td>
            <td class="px-4 py-2">
              <span v-if="c.deleted_at" class="inline-flex items-center rounded bg-red-50 px-2 py-0.5 text-red-700">Удалён</span>
              <span v-else class="inline-flex items-center rounded bg-green-50 px-2 py-0.5 text-green-700">Активен</span>
            </td>
            <td class="px-4 py-2 text-right">
              <div class="inline-flex items-center gap-2">
                <Link :href="`/admin/colors/${c.id}/edit`" class="rounded-md border px-2 py-1 text-xs hover:bg-gray-50">Редактировать</Link>
                <button v-if="!c.deleted_at" @click="remove(c.id)" class="rounded-md border px-2 py-1 text-xs hover:bg-red-50 text-red-700">Удалить</button>
                <button v-else @click="restore(c.id)" class="rounded-md border px-2 py-1 text-xs hover:bg-gray-50">Восстановить</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="colors.links?.length" class="flex justify-between items-center p-3 border-t text-sm">
        <div>Всего: {{ colors.total }}</div>
        <div class="flex flex-wrap gap-1">
          <button
            v-for="l in colors.links"
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
import { reactive, computed } from 'vue'

const page = usePage()
const colors = computed(() => page.props.colors)
const filters = computed(() => page.props.filters)
const flash = computed(() => page.props.flash)
const errors = computed(() => page.props.errors)

const local = reactive({
  search: filters.value?.search ?? '',
  show_deleted: !!(filters.value?.show_deleted ?? false),
})

const applyFilters = () => {
  router.get('/admin/colors', { search: local.search, show_deleted: local.show_deleted }, { preserveScroll: true })
}

const visit = (url) => {
  router.get(url, {}, { preserveScroll: true })
}

const remove = (id) => {
  router.delete(`/admin/colors/${id}`, { preserveScroll: true })
}

const restore = (id) => {
  router.post(`/admin/colors/${id}/restore`, {}, { preserveScroll: true })
}
</script>
