<template>
  <component
    :is="href ? Link : 'button'"
    :href="href || undefined"
    :type="href ? undefined : type"
    :disabled="disabled"
    class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm transition-colors focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
    :class="classes"
  >
    <slot />
  </component>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  href: { type: String, default: null },
  type: { type: String, default: 'button' },
  variant: { type: String, default: 'primary' }, // primary | outline | danger | ghost
  disabled: { type: Boolean, default: false },
})

const classes = computed(() => {
  switch (props.variant) {
    case 'outline':
      return 'border border-[var(--primary)] text-[var(--primary)] bg-[var(--bg)] hover:bg-[var(--primary)] hover:text-white'
    case 'danger':
      return 'bg-[var(--text)] text-white hover:opacity-90'
    case 'ghost':
      return 'border border-transparent text-[var(--text)] bg-transparent hover:bg-black/5'
    default:
      return 'bg-[var(--primary)] text-white hover:opacity-90'
  }
})
</script>
