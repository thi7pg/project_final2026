<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { adminService } from '../../services/adminService'
import { toFormData } from '../../utils/formData'
import SkeletonLoader from '../../components/ui/SkeletonLoader.vue'

const toast = useToast()
const loading = ref(true)
const saving = ref(false)
const logoFile = ref(null)
const currentLogo = ref(null)

const form = ref({
  name: '',
  address: '',
  phone: '',
  email: '',
  currency: 'USD',
  tax_percentage: 0,
  service_charge_percentage: 0,
  opening_time: '',
  closing_time: '',
})

async function load() {
  loading.value = true
  try {
    const res = await adminService.getSettings()
    const s = res.data
    form.value = {
      name: s.name ?? '',
      address: s.address ?? '',
      phone: s.phone ?? '',
      email: s.email ?? '',
      currency: s.currency ?? 'USD',
      tax_percentage: s.tax_percentage ?? 0,
      service_charge_percentage: s.service_charge_percentage ?? 0,
      opening_time: s.opening_time ?? '',
      closing_time: s.closing_time ?? '',
    }
    currentLogo.value = s.logo
  } catch (e) {
    toast.error(e.message)
  } finally {
    loading.value = false
  }
}
onMounted(load)

function onFileChange(event) {
  logoFile.value = event.target.files[0] ?? null
}

async function save() {
  saving.value = true
  try {
    const payload = toFormData({ ...form.value, logo: logoFile.value })
    await adminService.updateSettings(payload)
    toast.success('Settings saved')
    await load()
  } catch (e) {
    toast.error(e.message)
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="flex max-w-2xl flex-col gap-6">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Restaurant Settings</h1>

    <div v-if="loading" class="flex flex-col gap-3">
      <SkeletonLoader variant="line" :count="6" />
    </div>

    <form v-else class="card flex flex-col gap-4 p-5" @submit.prevent="save">
      <div class="flex items-center gap-4">
        <div class="h-16 w-16 overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
          <img v-if="currentLogo" :src="currentLogo" alt="Logo" class="h-full w-full object-cover" />
        </div>
        <div class="flex-1">
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Logo</label>
          <input
            type="file"
            accept="image/*"
            class="w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-primary-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-primary-700 dark:text-slate-300 dark:file:bg-primary-500/10 dark:file:text-primary-400"
            @change="onFileChange"
          />
        </div>
      </div>

      <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Restaurant Name</label>
        <input v-model="form.name" type="text" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
      </div>

      <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Address</label>
        <input v-model="form.address" type="text" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Phone</label>
          <input v-model="form.phone" type="tel" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Email</label>
          <input v-model="form.email" type="email" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
        </div>
      </div>

      <div class="grid grid-cols-3 gap-3">
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Currency</label>
          <input v-model="form.currency" type="text" maxlength="3" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm uppercase focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Tax %</label>
          <input v-model.number="form.tax_percentage" type="number" min="0" max="100" step="0.1" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Service %</label>
          <input v-model.number="form.service_charge_percentage" type="number" min="0" max="100" step="0.1" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Opening Time</label>
          <input v-model="form.opening_time" type="time" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Closing Time</label>
          <input v-model="form.closing_time" type="time" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
        </div>
      </div>

      <button
        type="submit"
        :disabled="saving"
        class="mt-2 w-full rounded-xl bg-primary-600 py-3 text-sm font-semibold text-white hover:bg-primary-500 disabled:opacity-50"
      >
        {{ saving ? 'Saving…' : 'Save Settings' }}
      </button>
    </form>
  </div>
</template>
