<template>
  <div>
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-600">*</span>
    </label>
    <select
      :id="id"
      class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900 disabled:cursor-not-allowed disabled:bg-gray-100"
      :class="selectClass"
      :disabled="disabled"
      :value="modelValue"
      @change="$emit('update:modelValue', cast($event.target.value))"
    >
      <option v-if="placeholder !== null" :value="''" disabled>{{ placeholder }}</option>
      <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
    </select>
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    <p v-else-if="hint" class="mt-1 text-sm text-gray-500">{{ hint }}</p>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: [String, Number, null],
  label: { type: String, default: '' },
  options: { type: Array, default: () => [] }, // [{label, value}]
  placeholder: { type: String, default: null },
  error: { type: String, default: '' },
  hint: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  required: { type: Boolean, default: false },
  id: { type: String, default: undefined },
  selectClass: { type: [String, Array, Object], default: '' },
})

const emit = defineEmits(['update:modelValue'])

const cast = (val) => {
  if (val === '') return null
  if (!isNaN(Number(val))) return Number(val)
  return val
}
</script>
