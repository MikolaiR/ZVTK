<template>
  <AdminLayout>
    <div class="max-w-2xl">
      <h2 class="text-lg font-semibold mb-4">Создание пользователя</h2>

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
          <label class="block text-sm font-medium mb-1">Пароль</label>
          <input v-model="form.password" type="password" class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900" />
          <p v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</p>
        </div>

        <div class="flex items-center gap-2">
          <input id="is_active" v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-gray-900 focus:ring-gray-900" />
          <label for="is_active" class="text-sm">Активен</label>
        </div>

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
          <button type="submit" :disabled="form.processing" class="rounded-md bg-gray-900 text-white px-4 py-2 text-sm disabled:opacity-50">Создать</button>
          <Link href="/admin/users" class="text-sm text-gray-600 hover:text-gray-900">Отмена</Link>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '../../../Layouts/AdminLayout.vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'

const page = usePage()
const roles = page.props.roles

const form = useForm({
  name: '',
  email: '',
  password: '',
  is_active: true,
  roles: [],
})

const submit = () => {
  form.post('/admin/users')
}
</script>
