<template>
  <div class="min-h-screen bg-[var(--bg)] text-[var(--text)]">
    <header class="sticky top-0 z-10 border-b border-[var(--border)] bg-white">
      <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <Link href="/" class="text-lg font-semibold tracking-tight text-[var(--text)]">AutoDom</Link>
        </div>
        <div class="flex-1 flex flex-col items-end gap-2">
          <div class="w-full flex items-center justify-end text-xs sm:text-sm text-[var(--primary)] gap-3">
            <span class="hidden sm:inline text-[var(--text)]">Техподдержка:</span>
            <a href="tel:+70000000000" class="text-[var(--primary)] hover:text-[var(--text)]">+7 000 000-00-00</a>
            <a href="#" class="text-[var(--primary)] hover:text-[var(--text)]">Telegram</a>
            <a href="#" class="text-[var(--primary)] hover:text-[var(--text)]">Viber</a>
          </div>
          <div class="w-full flex items-center justify-end gap-2">
            <input
              v-model="search"
              @input="onSearchInput"
              type="text"
              placeholder="Поиск по VIN"
              class="w-full max-w-md rounded-md border border-[var(--border)] focus:border-[var(--primary)] focus:ring-[var(--primary)]"
            />
            <ClientButton href="/profile" class="hidden sm:inline" variant="outline">Профиль</ClientButton>
            <ClientButton @click="logout" variant="danger">Выйти</ClientButton>
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
import ClientButton from '../Components/ClientButton.vue'

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
