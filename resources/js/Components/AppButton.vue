<template>
  <button
    :type="type"
    :disabled="disabled"
    class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm transition-colors focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
    :class="classes"
  >
    <slot />
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: { type: String, default: 'button' },
  variant: { type: String, default: 'primary' }, // primary | outline | danger | ghost
  disabled: { type: Boolean, default: false },
})

const classes = computed(() => {
  switch (props.variant) {
    case 'outline':
      return 'border bg-white hover:bg-gray-50'
    case 'danger':
      return 'bg-red-600 text-white hover:bg-red-700'
    case 'ghost':
      return 'border border-transparent hover:bg-gray-50'
    default:
      return 'bg-gray-900 text-white hover:bg-black'
  }
})
</script>
