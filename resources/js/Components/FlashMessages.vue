<template>
  <div class="text-sm space-y-1">
    <div v-if="flash?.success" class="text-green-600">{{ flash.success }}</div>
    <div v-if="flash?.error" class="text-red-600">{{ flash.error }}</div>
    <div v-if="errors?.message" class="text-red-600">{{ errors.message }}</div>
  </div>
</template>

<script setup>
const props = defineProps({
  flash: { type: Object, default: () => ({}) },
  errors: { type: Object, default: () => ({}) },
})
</script>
