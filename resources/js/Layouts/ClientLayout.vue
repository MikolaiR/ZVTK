<template>
  <div class="min-h-screen bg-gray-50 text-gray-900">
    <header class="sticky top-0 z-10 border-b bg-white/90 backdrop-blur">
      <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <Link href="/" class="text-lg font-semibold tracking-tight">AutoDom</Link>
        </div>
        <div class="flex-1 flex flex-col items-end gap-2">
          <div class="w-full flex items-center justify-end text-xs sm:text-sm text-gray-600 gap-3">
            <span class="hidden sm:inline">Техподдержка:</span>
            <a href="tel:+70000000000" class="hover:text-gray-900">+7 000 000-00-00</a>
            <a href="#" class="hover:text-gray-900">Telegram</a>
            <a href="#" class="hover:text-gray-900">Viber</a>
          </div>
          <div class="w-full flex items-center justify-end gap-2">
            <input
              v-model="search"
              @input="onSearchInput"
              type="text"
              placeholder="Поиск по VIN"
              class="w-full max-w-md rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900"
            />
            <Link href="/profile" class="hidden sm:inline rounded-md border px-3 py-1.5 hover:bg-gray-100">Профиль</Link>
            <button @click="logout" class="rounded-md border px-3 py-1.5 hover:bg-gray-100">Выйти</button>
          </div>
        </div>
      </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-6">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const search = ref('')
let t = null

const onSearchInput = () => {
  if (t) clearTimeout(t)
  t = setTimeout(() => {
    router.visit('/autos', {
      data: { vin: search.value },
      preserveState: true,
      preserveScroll: true,
      replace: true,
    })
  }, 400)
}

const logout = () => router.post('/logout')
</script>
