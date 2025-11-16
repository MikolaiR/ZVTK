<template>
  <AdminLayout>
    <div class="max-w-2xl">
      <h2 class="text-lg font-semibold mb-4">Редактирование пермишена</h2>

      <form @submit.prevent="submit" class="space-y-4">
        <FormInput label="Название" v-model="form.name" :error="form.errors.name" />

        <div>
          <div class="flex items-center gap-2 mb-2">
            <span class="text-sm font-medium">Роли</span>
            <Link href="/admin/roles/create" class="text-xs rounded-md border px-2 py-0.5 hover:bg-gray-50">Добавить роль</Link>
          </div>
          <FormCheckboxGroup v-model="form.roles" :options="roles" :error="form.errors.roles" />
        </div>

        <div class="flex items-center gap-3">
          <AppButton type="submit" :disabled="form.processing">Сохранить</AppButton>
          <Link href="/admin/permissions" class="text-sm text-gray-600 hover:text-gray-900">Отмена</Link>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '../../../Layouts/AdminLayout.vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import FormInput from '../../../Components/FormInput.vue'
import FormCheckboxGroup from '../../../Components/FormCheckboxGroup.vue'
import AppButton from '../../../Components/AppButton.vue'

const page = usePage()
const permission = page.props.permission
const roles = page.props.roles

const form = useForm({
  name: permission.name,
  roles: Array.from(permission.roles || []),
})

const submit = () => {
  form.put(`/admin/permissions/${permission.id}`)
}
</script>
