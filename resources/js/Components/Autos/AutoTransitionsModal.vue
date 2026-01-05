<template>
  <Modal :open="open" :title="title" @close="emit('close')">
    <div class="space-y-4">
      <template v-if="action === 'move_to_customs' || action === 'move_to_customs_from_parking'">
        <div>
          <label class="block text-sm mb-1">Таможня</label>
          <select
            v-model="form.customer_id"
            class="w-full rounded-md border border-[var(--border)] focus:border-[var(--primary)] focus:ring-[var(--primary)]"
          >
            <option :value="null" disabled>Выберите таможню</option>
            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <p v-if="errors?.customer_id" class="text-sm text-red-600 mt-1">{{ errors.customer_id }}</p>
        </div>
        <div>
          <label class="block text-sm mb-1">Дата прибытия</label>
          <input
            type="date"
            v-model="form.arrival_date"
            class="w-full rounded-md border border-[var(--border)] focus:border-[var(--primary)] focus:ring-[var(--primary)]"
          />
          <p v-if="errors?.arrival_date" class="text-sm text-red-600 mt-1">{{ errors.arrival_date }}</p>
        </div>
        <Uploads
          :upload="upload"
          :errors="errors"
          @drop-file="(...args) => emit('drop-file', ...args)"
          @pick-file="(...args) => emit('pick-file', ...args)"
          @remove="(...args) => emit('remove', ...args)"
        />
        <div>
          <label class="block text-sm mb-1">Комментарий</label>
          <textarea
            v-model="form.note"
            rows="3"
            class="w-full rounded-md border border-[var(--border)] focus:border-[var(--primary)] focus:ring-[var(--primary)]"
          ></textarea>
        </div>
      </template>

      <template v-else-if="action === 'move_to_parking' || action === 'move_to_other_parking'">
        <div>
          <label class="block text-sm mb-1">Стоянка</label>
          <select
            v-model="form.parking_id"
            class="w-full rounded-md border border-[var(--border)] focus:border-[var(--primary)] focus:ring-[var(--primary)]"
          >
            <option :value="null" disabled>Выберите стоянку</option>
            <option v-for="p in parkingOptions" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
          <p v-if="errors?.parking_id" class="text-sm text-red-600 mt-1">{{ errors.parking_id }}</p>
        </div>
        <Uploads
          :upload="upload"
          :errors="errors"
          @drop-file="(...args) => emit('drop-file', ...args)"
          @pick-file="(...args) => emit('pick-file', ...args)"
          @remove="(...args) => emit('remove', ...args)"
        />
        <div>
          <label class="block text-sm mb-1">Комментарий</label>
          <textarea
            v-model="form.note"
            rows="3"
            class="w-full rounded-md border border-[var(--border)] focus:border-[var(--primary)] focus:ring-[var(--primary)]"
          ></textarea>
        </div>
      </template>

      <template v-else-if="action === 'accept_at_parking'">
        <Uploads
          :upload="upload"
          :errors="errors"
          @drop-file="(...args) => emit('drop-file', ...args)"
          @pick-file="(...args) => emit('pick-file', ...args)"
          @remove="(...args) => emit('remove', ...args)"
        />
        <div>
          <label class="block text-sm mb-1">Комментарий</label>
          <textarea
            v-model="form.note"
            rows="3"
            class="w-full rounded-md border border-[var(--border)] focus:border-[var(--primary)] focus:ring-[var(--primary)]"
          ></textarea>
        </div>
      </template>

      <template v-else-if="action === 'sell'">
        <div>
          <label class="block text-sm mb-1">Дата передачи</label>
          <input
            type="date"
            v-model="form.sold_at"
            class="w-full rounded-md border border-[var(--border)] focus:border-[var(--primary)] focus:ring-[var(--primary)]"
          />
          <p v-if="errors?.sold_at" class="text-sm text-red-600 mt-1">{{ errors.sold_at }}</p>
        </div>
        <Uploads
          :upload="upload"
          :errors="errors"
          @drop-file="(...args) => emit('drop-file', ...args)"
          @pick-file="(...args) => emit('pick-file', ...args)"
          @remove="(...args) => emit('remove', ...args)"
        />
        <div>
          <label class="block text-sm mb-1">Комментарий</label>
          <textarea
            v-model="form.note"
            rows="3"
            class="w-full rounded-md border border-[var(--border)] focus:border-[var(--primary)] focus:ring-[var(--primary)]"
          ></textarea>
        </div>
      </template>

      <template v-else-if="action === 'save_files'">
        <Uploads
          :upload="upload"
          :errors="errors"
          @drop-file="(...args) => emit('drop-file', ...args)"
          @pick-file="(...args) => emit('pick-file', ...args)"
          @remove="(...args) => emit('remove', ...args)"
        />
      </template>
    </div>

    <template #footer>
      <ClientButton variant="outline" @click="emit('close')">Отмена</ClientButton>
      <ClientButton :disabled="processing || submitDisabled" @click="emit('submit')">Подтвердить</ClientButton>
    </template>
  </Modal>
</template>

<script setup>
import { computed } from 'vue'
import Modal from '../Modal.vue'
import ClientButton from '../ClientButton.vue'
import Uploads from '../Uploads.vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  action: { type: String, default: '' },
  form: { type: Object, required: true },
  upload: { type: Object, required: true },
  customers: { type: Array, default: () => [] },
  parkings: { type: Array, default: () => [] },
  availableParkings: { type: Array, default: () => [] },
  errors: { type: Object, default: () => ({}) },
  processing: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'submit', 'drop-file', 'pick-file', 'remove'])

const title = computed(() => {
  switch (props.action) {
    case 'move_to_customs':
      return 'Переместить на таможню'
    case 'move_to_parking':
      return 'Переместить на стоянку'
    case 'accept_at_parking':
      return 'Принять на стоянку'
    case 'move_to_customs_from_parking':
      return 'Переместить на таможню'
    case 'move_to_other_parking':
      return 'Переместить на другую стоянку'
    case 'sell':
      return 'Передана владельцу'
    case 'save_files':
      return 'Добавление файлов'
    default:
      return 'Действие'
  }
})

const parkingOptions = computed(() => (props.action === 'move_to_other_parking' ? props.availableParkings : props.parkings))

const submitDisabled = computed(() => {
  if (props.action === 'move_to_customs' || props.action === 'move_to_customs_from_parking') {
    return !props.form.customer_id
  }
  if (props.action === 'move_to_parking' || props.action === 'move_to_other_parking') {
    return !props.form.parking_id
  }
  return false
})
</script>
