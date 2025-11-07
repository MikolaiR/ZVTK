<template>
  <div class="max-w-xl">
    <h1 class="text-xl font-semibold mb-4">Профиль</h1>

    <div class="space-y-4 bg-white border rounded-lg p-4">
      <div>
        <div class="text-xs text-gray-500">Имя</div>
        <div class="mt-1">{{ user.name }}</div>
      </div>
      <div>
        <div class="text-xs text-gray-500">Email</div>
        <div class="mt-1">{{ user.email }}</div>
      </div>
      <div>
        <div class="text-xs text-gray-500">Компания</div>
        <div class="mt-1">{{ company?.name || '—' }}</div>
      </div>
      <div class="text-sm text-gray-500">Редактирование профиля возможно только через админ-панель.</div>
    </div>
  </div>
</template>

<script setup>
import ClientLayout from '../../Layouts/ClientLayout.vue'

defineOptions({ layout: ClientLayout })

defineProps({
  user: { type: Object, required: true },
  company: { type: Object, default: null },
})
</script>
