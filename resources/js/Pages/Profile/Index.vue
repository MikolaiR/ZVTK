<template>
  <div class="max-w-xl">
    <PageHeader title="Профиль" />

    <Card>
      <div class="space-y-4">
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
    </Card>
  </div>
</template>

<script setup>
import ClientLayout from '../../Layouts/ClientLayout.vue'
import Card from '../../Components/Card.vue'
import PageHeader from '../../Components/PageHeader.vue'

defineOptions({ layout: ClientLayout })

defineProps({
  user: { type: Object, required: true },
  company: { type: Object, default: null },
})
</script>
