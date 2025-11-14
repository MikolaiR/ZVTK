<template>
  <div v-if="links?.length" class="flex justify-between items-center p-3 border-t text-sm">
    <div v-if="total !== null && total !== undefined">Всего: {{ total }}</div>
    <div class="flex flex-wrap gap-1 ml-auto">
      <button
        v-for="l in links"
        :key="(l.url || '') + l.label"
        :disabled="!l.url"
        @click="visit(l.url)"
        class="px-3 py-1 rounded border hover:bg-gray-50 disabled:opacity-50"
        :class="l.active ? 'bg-gray-900 text-white hover:bg-gray-900' : ''"
      >
        <span v-html="l.label" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
  links: { type: Array, default: () => [] },
  total: { type: Number, default: null },
})

const visit = (url) => {
  if (!url) return
  router.get(url, {}, { preserveScroll: true })
}
</script>
