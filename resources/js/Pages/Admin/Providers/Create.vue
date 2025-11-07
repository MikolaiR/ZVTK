<template>
  <AdminLayout>
    <div class="mb-6 flex items-center justify-between">
      <h2 class="text-lg font-semibold">Создать поставщика</h2>
      <Link href="/admin/providers" class="text-sm text-gray-600 hover:text-gray-900">← Назад к списку</Link>
    </div>

    <div class="rounded-lg border bg-white p-6 max-w-xl space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Наименование</label>
        <input v-model="form.name" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900" />
        <div v-if="errors?.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Пользователь (необязательно)</label>
        <select v-model.number="form.user_id" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
          <option :value="null">— Без пользователя —</option>
          <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
        </select>
        <div v-if="errors?.user_id" class="mt-1 text-sm text-red-600">{{ errors.user_id }}</div>
      </div>

      <div class="flex gap-3">
        <button @click="save" class="rounded-md bg-gray-900 text-white px-4 py-2 text-sm">Сохранить</button>
        <Link href="/admin/providers" class="rounded-md border px-4 py-2 text-sm hover:bg-gray-50">Отмена</Link>
      </div>

      <div class="mt-3 text-sm">
        <div v-if="flash.success" class="text-green-600">{{ flash.success }}</div>
        <div v-if="flash.error" class="text-red-600">{{ flash.error }}</div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '../../../Layouts/AdminLayout.vue'
import { router, usePage, Link } from '@inertiajs/vue3'
import { reactive, computed } from 'vue'

const page = usePage()
const users = computed(() => page.props.users || [])
const flash = computed(() => page.props.flash)
const errors = computed(() => page.props.errors)

const form = reactive({
  name: '',
  user_id: null,
})

const save = () => {
  router.post('/admin/providers', form)
}
</script>
