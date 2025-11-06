<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <div class="w-full max-w-md bg-white border rounded-lg p-6 shadow-sm">
      <h1 class="text-xl font-semibold mb-1">Вход в систему</h1>
      <p class="text-sm text-gray-600 mb-6">Укажите свои учетные данные</p>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1" for="email">Email</label>
          <input
            v-model="form.email"
            id="email"
            type="email"
            autocomplete="email"
            required
            class="block w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900"
          />
          <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1" for="password">Пароль</label>
          <input
            v-model="form.password"
            id="password"
            type="password"
            autocomplete="current-password"
            required
            class="block w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900"
          />
          <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
        </div>

        <div class="flex items-center justify-between">
          <label class="inline-flex items-center gap-2 text-sm">
            <input v-model="form.remember" type="checkbox" class="rounded border-gray-300 text-gray-900 focus:ring-gray-900" />
            Запомнить меня
          </label>
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="w-full inline-flex justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black disabled:opacity-50"
        >
          Войти
        </button>

        <p v-if="form.errors.email && !form.errors.password" class="text-sm text-red-600">{{ form.errors.email }}</p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post('/login')
}
</script>
