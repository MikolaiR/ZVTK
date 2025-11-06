<template>
  <AdminLayout>
    <div class="max-w-2xl">
      <h2 class="text-lg font-semibold mb-4">Редактирование пользователя</h2>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Имя</label>
          <input v-model="form.name" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900" />
          <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Email</label>
          <input v-model="form.email" type="email" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900" />
          <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Пароль (не заполняйте, если не меняете)</label>
          <input v-model="form.password" type="password" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900" />
          <p v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</p>
        </div>

        <div class="flex items-center gap-2">
          <input id="is_active" v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-gray-900 focus:ring-gray-900" :disabled="meId === user.id" />
          <label for="is_active" class="text-sm">Активен</label>
        </div>
        <p v-if="meId === user.id" class="text-xs text-gray-500">Нельзя выключить самого себя.</p>

        <div>
          <label class="block text-sm font-medium mb-2">Роли</label>
          <div class="flex flex-wrap gap-3">
            <label v-for="r in roles" :key="r" class="inline-flex items-center gap-2 text-sm">
              <input type="checkbox" :value="r" v-model="form.roles" class="rounded border-gray-300 text-gray-900 focus:ring-gray-900" />
              <span>{{ r }}</span>
            </label>
          </div>
          <p v-if="form.errors.roles" class="text-sm text-red-600 mt-1">{{ form.errors.roles }}</p>
        </div>

        <div class="flex items-center gap-3">
          <button type="submit" :disabled="form.processing" class="rounded-md bg-gray-900 text-white px-4 py-2 text-sm disabled:opacity-50">Сохранить</button>
          <Link href="/admin/users" class="text-sm text-gray-600 hover:text-gray-900">Отмена</Link>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '../../../Layouts/AdminLayout.vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const user = page.props.user
const roles = page.props.roles
const meId = computed(() => page.props.auth?.user?.id ?? null)

const form = useForm({
  name: user.name,
  email: user.email,
  password: '', // optional
  is_active: user.is_active,
  roles: Array.from(user.roles || []),
})

const submit = () => {
  form.put(`/admin/users/${user.id}`)
}
</script>
