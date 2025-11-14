<template>
  <div>
    <label v-if="label" class="block text-sm font-medium text-gray-700 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-600">*</span>
    </label>
    <div class="flex flex-wrap gap-3">
      <label
        v-for="opt in normalizedOptions"
        :key="opt.value"
        class="inline-flex items-center gap-2 text-sm"
      >
        <input
          type="checkbox"
          class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
          :value="opt.value"
          :checked="(modelValue || []).includes(opt.value)"
          @change="onToggle(opt.value, $event.target.checked)"
        />
        <span>{{ opt.label }}</span>
      </label>
    </div>
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    <p v-else-if="hint" class="mt-1 text-sm text-gray-500">{{ hint }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({
  modelValue: { type: Array, default: () => [] }, // array of values
  options: { type: Array, default: () => [] }, // array of string or {label,value}
  label: { type: String, default: '' },
  error: { type: String, default: '' },
  hint: { type: String, default: '' },
  required: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const normalizedOptions = computed(() => {
  return props.options.map((o) =>
    typeof o === 'string' ? { label: o, value: o } : { label: o.label ?? o.value, value: o.value ?? o.label }
  )
})

const onToggle = (value, checked) => {
  const next = new Set(props.modelValue || [])
  if (checked) next.add(value)
  else next.delete(value)
  emit('update:modelValue', Array.from(next))
}
</script>
