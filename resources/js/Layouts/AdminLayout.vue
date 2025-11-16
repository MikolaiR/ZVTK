<template>
  <div class="min-h-screen bg-gray-50">
    <header class="sticky top-0 z-10 border-b bg-white/80 backdrop-blur">
      <div class="mx-auto max-w-7xl px-4 py-4 flex items-center justify-between">
        <div class="flex items-center gap-6">
          <h1 class="text-lg font-semibold">Admin</h1>
          <nav class="hidden md:flex items-center gap-4 text-sm text-gray-600">
            <Link href="/admin" class="hover:text-gray-900">Главная</Link>
            <Link href="/admin/users" class="hover:text-gray-900">Пользователи</Link>
            <Link href="/admin/roles" class="hover:text-gray-900">Роли</Link>
            <Link href="/admin/permissions" class="hover:text-gray-900">Пермишены</Link>
            <Link href="/admin/autos" class="hover:text-gray-900">Автомобили</Link>
            <Link href="/admin/customers" class="hover:text-gray-900">Таможни</Link>
            <Link href="/admin/parkings" class="hover:text-gray-900">Стоянки</Link>
            <Link href="/admin/auto-brands" class="hover:text-gray-900">Бренды</Link>
            <Link href="/admin/auto-models" class="hover:text-gray-900">Модели</Link>
            <Link href="/admin/companies" class="hover:text-gray-900">Компании</Link>
            <Link href="/admin/colors" class="hover:text-gray-900">Цвета</Link>
            <Link href="/admin/providers" class="hover:text-gray-900">Поставщики</Link>
          </nav>
        </div>
        <div class="flex items-center gap-4 text-sm">
          <span class="hidden sm:inline text-gray-600">{{ page.props.auth?.user?.email }}</span>
          <button @click="logout" class="rounded-md border px-3 py-1.5 hover:bg-gray-100">Выйти</button>
        </div>
      </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
const page = usePage()

const logout = () => {
  router.post('/logout')
}
</script>
