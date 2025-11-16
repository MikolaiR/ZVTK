<template>
  <teleport to="body">
    <div v-if="open" class="fixed inset-0 z-50">
      <div class="absolute inset-0 bg-black/30" @click="emit('close')"></div>
      <div class="absolute inset-0 grid place-items-center p-4">
        <div :class="['w-full rounded-lg border bg-[var(--bg)] text-[var(--text)]', sizeClass]">
          <div v-if="title || $slots.header" class="px-4 py-3 border-b flex items-center justify-between">
            <h3 v-if="title" class="text-lg font-semibold">{{ title }}</h3>
            <slot v-else name="header" />
            <button class="ml-auto text-sm" @click="emit('close')">✕</button>
          </div>
          <div class="p-4">
            <slot />
          </div>
          <div v-if="$slots.footer" class="px-4 py-3 border-t flex items-center justify-end gap-2">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { onMounted, onBeforeUnmount, computed } from 'vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: '' },
  size: { type: String, default: 'md' }, // md | lg
})
const emit = defineEmits(['close'])

const sizeClass = computed(() => (props.size === 'lg' ? 'max-w-3xl' : 'max-w-xl'))

const onKey = (e) => { if (e.key === 'Escape') emit('close') }
onMounted(() => window.addEventListener('keydown', onKey))
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))
</script>
