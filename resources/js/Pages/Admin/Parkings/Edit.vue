<template>
  <AdminLayout>
    <div class="max-w-4xl">
      <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Редактирование стоянки</h1>
        <Link href="/admin/parkings" class="text-sm rounded-md border px-3 py-1.5 hover:bg-gray-100">К списку</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <FormInput label="Название" v-model="form.name" :error="form.errors.name" />
          <FormSelect label="Компания" v-model.number="form.company_id" :options="companyOptions" placeholder="Не выбрана" :error="form.errors.company_id" />
          <FormInput class="md:col-span-2" label="Адрес" v-model="form.address" :error="form.errors.address" />
        </div>

        <section>
          <h4 class="text-base font-medium mb-2">Прайсы</h4>

          <div class="overflow-x-auto rounded-lg border bg-white mb-3">
            <table class="min-w-full text-sm">
              <thead class="bg-gray-50 text-left">
                <tr>
                  <th class="px-3 py-2">Название</th>
                  <th class="px-3 py-2">Цена</th>
                  <th class="px-3 py-2">Начало</th>
                  <th class="px-3 py-2">Окончание</th>
                  <th class="px-3 py-2 text-right">Действия</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="pr in prices" :key="pr.id" class="border-t">
                  <td class="px-3 py-2">
                    <template v-if="editingId === pr.id">
                      <FormInput v-model="editPriceForm.name" />
                    </template>
                    <template v-else>{{ pr.name }}</template>
                  </td>
                  <td class="px-3 py-2">
                    <template v-if="editingId === pr.id">
                      <FormInput type="number" min="0" v-model.number="editPriceForm.price" />
                    </template>
                    <template v-else>{{ pr.price ?? '—' }}</template>
                  </td>
                  <td class="px-3 py-2">
                    <template v-if="editingId === pr.id">
                      <FormInput type="date" v-model="editPriceForm.date_start" />
                    </template>
                    <template v-else>{{ pr.date_start }}</template>
                  </td>
                  <td class="px-3 py-2">
                    <template v-if="editingId === pr.id">
                      <FormInput type="date" v-model="editPriceForm.date_end" />
                    </template>
                    <template v-else>{{ pr.date_end || '—' }}</template>
                  </td>
                  <td class="px-3 py-2 text-right">
                    <div class="inline-flex items-center gap-2">
                      <template v-if="editingId === pr.id">
                        <AppButton @click.prevent="savePrice(pr)">Сохранить</AppButton>
                        <AppButton variant="outline" @click.prevent="cancelEdit">Отмена</AppButton>
                      </template>
                      <template v-else>
                        <AppButton variant="outline" @click.prevent="startEdit(pr)">Редактировать</AppButton>
                        <AppButton variant="danger" @click.prevent="destroyPrice(pr)">Удалить</AppButton>
                      </template>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <FormInput label="Название" v-model="priceForm.name" :error="priceForm.errors.name" />
            <FormInput label="Цена" type="number" min="0" v-model.number="priceForm.price" :error="priceForm.errors.price" />
            <FormInput label="Начало" type="date" v-model="priceForm.date_start" :error="priceForm.errors.date_start" />
            <FormInput label="Окончание" type="date" v-model="priceForm.date_end" :error="priceForm.errors.date_end" />
          </div>

          <div class="mt-3">
            <AppButton @click.prevent="createPrice" :disabled="priceForm.processing">Добавить прайс</AppButton>
          </div>
        </section>

        <div class="flex items-center gap-3">
          <AppButton type="submit" :disabled="form.processing">Сохранить</AppButton>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '../../../Layouts/AdminLayout.vue'
import { Link, useForm, usePage, router } from '@inertiajs/vue3'
import FormInput from '../../../Components/FormInput.vue'
import FormSelect from '../../../Components/FormSelect.vue'
import AppButton from '../../../Components/AppButton.vue'
import { computed, ref } from 'vue'

const page = usePage()
const parking = page.props.parking || {}
const reactiveParking = computed(() => page.props.parking || {})
const companies = page.props.companies || []

const companyOptions = computed(() => (companies || []).map(c => ({ label: c.name, value: c.id })))

const form = useForm({
  name: parking.name || '',
  address: parking.address || '',
  company_id: parking.company_id || null,
})

const prices = computed(() => Array.isArray(reactiveParking.value?.prices) ? reactiveParking.value.prices : [])

const submit = () => {
  form.put(`/admin/parkings/${parking.id}`)
}

// Prices inline management
const editingId = ref(null)
const editPriceForm = useForm({ name: '', price: null, date_start: '', date_end: null })

const startEdit = (pr) => {
  editingId.value = pr.id
  editPriceForm.name = pr.name
  editPriceForm.price = pr.price
  editPriceForm.date_start = pr.date_start
  editPriceForm.date_end = pr.date_end
}

const cancelEdit = () => {
  editingId.value = null
  editPriceForm.reset('name', 'price', 'date_start', 'date_end')
}

const savePrice = (pr) => {
  const payload = {
    name: editPriceForm.name,
    price: editPriceForm.price,
    date_start: editPriceForm.date_start,
    date_end: editPriceForm.date_end,
  }
  router.put(`/admin/parkings/${parking.id}/prices/${pr.id}`, payload, {
    preserveScroll: true,
    onSuccess: () => { editingId.value = null; router.reload({ only: ['parking'] }) },
  })
}

const priceForm = useForm({ name: '', price: null, date_start: '', date_end: null })
const createPrice = () => {
  priceForm.post(`/admin/parkings/${parking.id}/prices`, {
    preserveScroll: true,
    onSuccess: () => { priceForm.reset('name', 'price', 'date_start', 'date_end'); router.reload({ only: ['parking'] }) },
  })
}

const destroyPrice = (pr) => {
  if (!confirm('Удалить прайс?')) return
  router.delete(`/admin/parkings/${parking.id}/prices/${pr.id}`, {
    preserveScroll: true,
    onSuccess: () => router.reload({ only: ['parking'] })
  })
}
</script>
