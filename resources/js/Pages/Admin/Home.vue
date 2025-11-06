<template>
  <div class="min-h-screen bg-gray-50">
    <header class="border-b bg-white">
      <div class="mx-auto max-w-7xl px-4 py-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold">Admin • Home</h1>
        <div class="text-sm text-gray-600">{{ auth?.user?.email }}</div>
      </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 space-y-6">
      <section class="rounded-lg border bg-white p-6">
        <h2 class="text-base font-medium mb-2">Добро пожаловать в админ-панель</h2>
        <p class="text-sm text-gray-600">
          Здесь будет дашборд и быстрые действия.
        </p>
      </section>
    </main>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'
const { props } = usePage()
const auth = props.auth
</script>
