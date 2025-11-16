<template>
  <ClientLayout>
    <div>
      <h1 class="text-xl font-semibold mb-4">Список автомобилей</h1>

      <div class="overflow-hidden rounded-lg border bg-white">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Фото</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="a in autos.data" :key="a.id" class="hover:bg-gray-50">
              <td class="px-4 py-2">
                <AutoPreview :src="a.preview_url" sizeClass="h-12 w-20" />
              </td>
              <td class="px-4 py-2">
                <Link :href="`/autos/${a.id}`" class="text-gray-900 hover:underline">{{ a.title }}</Link>
              </td>
              <td class="px-4 py-2">
                <StatusBadge :status="a.status" :label="a.status_label" />
              </td>
            </tr>
            <tr v-if="!autos.data.length">
              <td colspan="3" class="px-4 py-6 text-center text-gray-500">Нет результатов</td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination class="mt-4" :links="autos.links" :total="autos.total" />
    </div>
  </ClientLayout>
</template>

<script setup>
import ClientLayout from '../../Layouts/ClientLayout.vue'
import { Link } from '@inertiajs/vue3'
import AutoPreview from '../../Components/AutoPreview.vue'
import StatusBadge from '../../Components/StatusBadge.vue'
import Pagination from '../../Components/Pagination.vue'

defineProps({
  autos: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})
</script>
