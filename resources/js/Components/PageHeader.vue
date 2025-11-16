<template>
  <div class="mb-4 flex items-center justify-between">
    <h1 class="text-xl font-semibold text-[#000]">{{ title }}</h1>
    <div>
      <slot name="actions" />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  title: { type: String, default: '' },
})
</script>
