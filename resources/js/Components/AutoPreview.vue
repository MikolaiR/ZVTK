<template>
  <div :class="['overflow-hidden border border-[var(--border)] rounded', sizeClass]">
    <img
      v-if="src"
      :src="src"
      :alt="alt"
      class="h-full w-full object-cover"
    />
    <div v-else class="h-full w-full bg-[var(--bg)] text-[var(--text)] flex items-center justify-center text-[10px]">
      Нет фото
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  src: { type: String, default: '' },
  alt: { type: String, default: 'Превью' },
  sizeClass: { type: String, default: 'h-12 w-20' },
})
</script>
