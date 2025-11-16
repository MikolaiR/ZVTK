<template>
  <AdminLayout>
    <div class="max-w-xl">
      <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Новая таможня</h1>
        <Link href="/admin/customers" class="text-sm rounded-md border px-3 py-1.5 hover:bg-gray-100">К списку</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-1 gap-4">
          <FormInput label="Название" v-model="form.name" :error="form.errors.name" />
          <FormInput label="Телефон" v-model="form.phone" :error="form.errors.phone" />
          <FormInput label="Email" type="email" v-model="form.email" :error="form.errors.email" />
          <FormInput label="Документ" v-model="form.document_number" :error="form.errors.document_number" />
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
import { Link, useForm } from '@inertiajs/vue3'
import FormInput from '../../../Components/FormInput.vue'
import AppButton from '../../../Components/AppButton.vue'

const form = useForm({ name: '', phone: '', email: '', document_number: '' })

const submit = () => { form.post('/admin/customers') }
</script>
