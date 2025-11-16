<template>
  <span class="inline-flex items-center rounded px-2 py-0.5 text-xs" :class="classes">
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: { type: [Number, String], default: null },
  label: { type: String, default: '' },
})

const classes = computed(() => {
  const s = Number(props.status)
  // 1 Delivery, 2 Customer, 3 DeliveryToParking, 4 Parking, 5 Sale
  const isBlue = s === 1 || s === 3
  return isBlue ? 'bg-[var(--primary)] text-white' : 'bg-[var(--text)] text-white'
})
</script>
