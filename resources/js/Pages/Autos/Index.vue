<template>
  <ClientLayout>
    <div>
      <h1 class="text-xl font-semibold mb-4">Список автомобилей</h1>

      <div class="overflow-hidden rounded-lg border bg-white">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">VIN</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="a in autos.data" :key="a.id" class="hover:bg-gray-50">
              <td class="px-4 py-2">{{ a.title }}</td>
              <td class="px-4 py-2 text-gray-600">{{ a.vin }}</td>
            </tr>
            <tr v-if="!autos.data.length">
              <td colspan="2" class="px-4 py-6 text-center text-gray-500">Нет результатов</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex flex-wrap gap-2">
        <Link
          v-for="link in autos.links"
          :key="link.label + link.url"
          :href="link.url || '#'"
          v-html="link.label"
          :class="[
            'px-3 py-1.5 rounded-md border text-sm',
            link.active ? 'bg-gray-900 text-white border-gray-900' : 'bg-white hover:bg-gray-50'
          ]"
          :disabled="!link.url"
        />
      </div>
    </div>
  </ClientLayout>
</template>

<script setup>
import ClientLayout from '../../Layouts/ClientLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
  autos: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})
</script>
