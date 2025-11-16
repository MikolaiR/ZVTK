<template>
  <ClientLayout>
    <div>
      <PageHeader title="Статусы">
        <template #actions>
          <ClientButton href="/autos/create" variant="outline" class="text-sm">Добавить авто</ClientButton>
        </template>
      </PageHeader>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <Link
          href="/autos"
          class="relative rounded-lg overflow-hidden border shadow-sm group h-36 flex items-end"
        >
          <div class="absolute inset-0 bg-[url('/images/not_photo.png')] bg-cover bg-center"></div>
          <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
          <div class="relative p-4 w-full text-white">
            <div class="text-sm opacity-80">Список</div>
            <div class="text-lg font-semibold">Все автомобили</div>
          </div>
        </Link>

        <Link
          v-for="s in statuses"
          :key="s.value"
          :href="`/autos?status=${s.value}`"
          class="relative rounded-lg overflow-hidden border shadow-sm group h-36 flex items-end"
        >
          <div class="absolute inset-0" :style="{ backgroundImage: `url(${s.background})`, backgroundSize: 'cover', backgroundPosition: 'center' }"></div>
          <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
          <div class="relative p-4 w-full text-white">
            <div class="text-sm opacity-80">Список</div>
            <div class="text-lg font-semibold">{{ s.label }}</div>
          </div>
        </Link>
      </div>
    </div>
  </ClientLayout>
</template>

<script setup>
import ClientLayout from '../Layouts/ClientLayout.vue'
import { Link } from '@inertiajs/vue3'
import PageHeader from '../Components/PageHeader.vue'
import ClientButton from '../Components/ClientButton.vue'

defineProps({
  statuses: { type: Array, default: () => [] },
})
</script>
