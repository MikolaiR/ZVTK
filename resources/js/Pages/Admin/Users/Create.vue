<template>
  <AdminLayout>
    <div class="max-w-2xl">
      <h2 class="text-lg font-semibold mb-4">Создание пользователя</h2>

      <form @submit.prevent="submit" class="space-y-4">
        <FormInput label="Имя" v-model="form.name" :error="form.errors.name" />
        <FormInput label="Email" type="email" v-model="form.email" :error="form.errors.email" />
        <FormInput label="Пароль" type="password" v-model="form.password" :error="form.errors.password" />

        <FormSelect
          label="Компания"
          v-model="form.company_id"
          :options="companyOptions"
          placeholder="Без компании"
          :error="form.errors.company_id"
        />

        <FormCheckboxGroup label="Роли" v-model="form.roles" :options="roles" :error="form.errors.roles" />
        <div>
          <div class="flex items-center gap-2 mb-2">
            <span class="text-sm font-medium">Пермишены</span>
            <Link href="/admin/permissions/create" class="text-xs rounded-md border px-2 py-0.5 hover:bg-gray-50">Добавить пермишен</Link>
          </div>
          <FormCheckboxGroup v-model="form.permissions" :options="permissions" :error="form.errors.permissions" />
        </div>

        <div class="flex items-center gap-3">
          <AppButton type="submit" :disabled="form.processing">Создать</AppButton>
          <Link href="/admin/users" class="text-sm text-gray-600 hover:text-gray-900">Отмена</Link>
          <div class="ml-auto hidden md:flex items-center gap-2">
            <Link href="/admin/roles/create" class="text-sm rounded-md border px-3 py-2 hover:bg-gray-50">Добавить роль</Link>
            <Link href="/admin/permissions/create" class="text-sm rounded-md border px-3 py-2 hover:bg-gray-50">Добавить пермишен</Link>
          </div>
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
import FormCheckboxGroup from '../../../Components/FormCheckboxGroup.vue'
import AppButton from '../../../Components/AppButton.vue'

const page = usePage()
const roles = page.props.roles
const permissions = page.props.permissions
const companies = page.props.companies || []

const form = useForm({
  name: '',
  email: '',
  password: '',
  company_id: '',
  roles: [],
  permissions: [],
})

const submit = () => {
  form.post('/admin/users')
}

const companyOptions = companies.map(c => ({ label: c.name, value: c.id }))
</script>
