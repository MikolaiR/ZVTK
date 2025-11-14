<template>
  <div class="flex items-center gap-2">
    <input
      :id="id"
      type="checkbox"
      class="rounded border-gray-300 text-gray-900 focus:ring-gray-900 disabled:cursor-not-allowed disabled:bg-gray-100"
      :checked="modelValue"
      :disabled="disabled"
      @change="$emit('update:modelValue', $event.target.checked)"
    />
    <label v-if="label" :for="id" class="text-sm">{{ label }}</label>
  </div>
  <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
  <p v-else-if="hint" class="mt-1 text-sm text-gray-500">{{ hint }}</p>
</template>

<script setup>
const props = defineProps({
  modelValue: { type: Boolean, default: false },
  label: { type: String, default: '' },
  error: { type: String, default: '' },
  hint: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  id: { type: String, default: undefined },
})
</script>
