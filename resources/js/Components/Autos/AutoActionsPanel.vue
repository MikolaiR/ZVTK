<template>
  <div class="space-y-2">
    <ClientButton
      v-for="a in actions"
      :key="a.key"
      class="w-full"
      :variant="a.variant"
      @click="emit('action', a.key)"
    >
      {{ a.label }}
    </ClientButton>
  </div>
</template>

<script setup>
import ClientButton from '../ClientButton.vue'

defineProps({
  actions: { type: Array, default: () => [] },
})

const emit = defineEmits(['action'])
</script>
