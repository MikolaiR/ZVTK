<template>
  <AdminLayout>
    <div class="max-w-xl">
      <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Новая стоянка</h1>
        <Link href="/admin/parkings" class="text-sm rounded-md border px-3 py-1.5 hover:bg-gray-100">К списку</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-1 gap-4">
          <FormInput label="Название" v-model="form.name" :error="form.errors.name" />
          <FormInput label="Адрес" v-model="form.address" :error="form.errors.address" />
          <FormSelect label="Компания" v-model.number="form.company_id" :options="companyOptions" placeholder="Не выбрана" :error="form.errors.company_id" />
        </div>

        <div class="flex items-center gap-3">
          <AppButton type="submit" :disabled="form.processing">Создать</AppButton>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '../../../Layouts/AdminLayout.vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import FormInput from '../../../Components/FormInput.vue'
import FormSelect from '../../../Components/FormSelect.vue'
import AppButton from '../../../Components/AppButton.vue'
import { computed } from 'vue'

const page = usePage()
const companies = page.props.companies || []
const companyOptions = computed(() => (companies || []).map(c => ({ label: c.name, value: c.id })))

const form = useForm({ name: '', address: '', company_id: null })

const submit = () => {
  form.post('/admin/parkings')
}
</script>
