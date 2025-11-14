<template>
  <div>
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-600">*</span>
    </label>
    <textarea
      :id="id"
      class="w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900 disabled:cursor-not-allowed disabled:bg-gray-100"
      :class="textareaClass"
      :rows="rows"
      :placeholder="placeholder"
      :autocomplete="autocomplete"
      :disabled="disabled"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    <p v-else-if="hint" class="mt-1 text-sm text-gray-500">{{ hint }}</p>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  error: { type: String, default: '' },
  hint: { type: String, default: '' },
  autocomplete: { type: String, default: 'off' },
  disabled: { type: Boolean, default: false },
  required: { type: Boolean, default: false },
  id: { type: String, default: undefined },
  rows: { type: Number, default: 3 },
  textareaClass: { type: [String, Array, Object], default: '' },
})
</script>
