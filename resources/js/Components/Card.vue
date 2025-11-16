<template>
  <div class="rounded-lg border bg-white">
    <div v-if="title || $slots.header" class="px-4 py-3 border-b flex items-center justify-between">
      <h2 v-if="title" class="text-base font-medium text-[#000]">{{ title }}</h2>
      <slot v-else name="header" />
      <slot name="header-extra" />
    </div>
    <div class="p-4">
      <slot />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  title: { type: String, default: '' },
})
</script>
