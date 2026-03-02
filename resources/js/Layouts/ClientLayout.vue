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
          <div ref="menuRoot" class="relative w-full flex items-center justify-end gap-2">
            <input
              v-model="search"
              @input="onSearchInput"
              type="text"
              placeholder="Поиск по VIN"
              class="w-full max-w-md rounded-md border border-[var(--border)] focus:border-[var(--primary)] focus:ring-[var(--primary)]"
            />

            <ClientButton @click="toggleMenu" variant="outline">Меню</ClientButton>

            <transition
              enter-active-class="transition duration-200 ease-out"
              enter-from-class="translate-x-2 opacity-0"
              enter-to-class="translate-x-0 opacity-100"
              leave-active-class="transition duration-150 ease-in"
              leave-from-class="translate-x-0 opacity-100"
              leave-to-class="translate-x-2 opacity-0"
            >
              <div
                v-if="menuOpen"
                class="absolute right-0 top-full z-20 mt-2 w-52 rounded-md border border-[var(--border)] bg-white p-2 shadow-lg"
              >
                <Link
                  href="/instructions"
                  @click="closeMenu"
                  class="block rounded-md px-3 py-2 text-sm text-[var(--text)] hover:bg-gray-100"
                >
                  Инструкция
                </Link>
                <Link
                  href="/profile"
                  @click="closeMenu"
                  class="mt-1 block rounded-md px-3 py-2 text-sm text-[var(--text)] hover:bg-gray-100"
                >
                  Профиль
                </Link>
                <button
                  type="button"
                  @click="onLogout"
                  class="mt-1 block w-full rounded-md px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50"
                >
                  Выйти
                </button>
              </div>
            </transition>
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
import { onBeforeUnmount, onMounted, ref } from 'vue'
import ClientButton from '../Components/ClientButton.vue'

const search = ref('')
const menuOpen = ref(false)
const menuRoot = ref(null)
let t = null

const toggleMenu = () => {
  menuOpen.value = !menuOpen.value
}

const closeMenu = () => {
  menuOpen.value = false
}

const onDocumentClick = (event) => {
  if (!menuOpen.value) return
  if (!menuRoot.value?.contains(event.target)) closeMenu()
}

const onDocumentKeydown = (event) => {
  if (event.key === 'Escape') closeMenu()
}

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

const onLogout = () => {
  closeMenu()
  router.post('/logout')
}

onMounted(() => {
  document.addEventListener('click', onDocumentClick)
  document.addEventListener('keydown', onDocumentKeydown)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', onDocumentClick)
  document.removeEventListener('keydown', onDocumentKeydown)
})
</script>
