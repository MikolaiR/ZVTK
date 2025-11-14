<template>
  <AdminLayout>
    <div class="max-w-2xl">
      <h2 class="text-lg font-semibold mb-4">Редактирование пользователя</h2>

      <form @submit.prevent="submit" class="space-y-4">
        <FormInput label="Имя" v-model="form.name" :error="form.errors.name" />
        <FormInput label="Email" type="email" v-model="form.email" :error="form.errors.email" />
        <FormInput label="Пароль (не заполняйте, если не меняете)" type="password" v-model="form.password" :error="form.errors.password" />

        <FormSelect
          label="Компания"
          v-model="form.company_id"
          :options="companyOptions"
          placeholder="Без компании"
          :error="form.errors.company_id"
        />

        <FormCheckbox :id="'is_active'" v-model="form.is_active" label="Активен" :disabled="meId === user.id" />
        <p v-if="meId === user.id" class="text-xs text-gray-500">Нельзя выключить самого себя.</p>

        <FormCheckboxGroup label="Роли" v-model="form.roles" :options="roles" :error="form.errors.roles" />
        <div>
          <div class="flex items-center gap-2 mb-2">
            <span class="text-sm font-medium">Пермишены</span>
            <Link href="/admin/permissions/create" class="text-xs rounded-md border px-2 py-0.5 hover:bg-gray-50">Добавить пермишен</Link>
          </div>
          <FormCheckboxGroup v-model="form.permissions" :options="permissions" :error="form.errors.permissions" />
        </div>

        <div class="flex items-center gap-3">
          <AppButton type="submit" :disabled="form.processing">Сохранить</AppButton>
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
import { computed } from 'vue'
import FormInput from '../../../Components/FormInput.vue'
import FormSelect from '../../../Components/FormSelect.vue'
import FormCheckbox from '../../../Components/FormCheckbox.vue'
import FormCheckboxGroup from '../../../Components/FormCheckboxGroup.vue'
import AppButton from '../../../Components/AppButton.vue'

const page = usePage()
const user = page.props.user
const roles = page.props.roles
const permissions = page.props.permissions
const companies = page.props.companies || []
const meId = computed(() => page.props.auth?.user?.id ?? null)

const form = useForm({
  name: user.name,
  email: user.email,
  password: '', // optional
  company_id: user.company_id ?? '',
  is_active: user.is_active,
  roles: Array.from(user.roles || []),
  permissions: Array.from(user.permissions || []),
})

const submit = () => {
  form.put(`/admin/users/${user.id}`)
}

const companyOptions = companies.map(c => ({ label: c.name, value: c.id }))
</script>
