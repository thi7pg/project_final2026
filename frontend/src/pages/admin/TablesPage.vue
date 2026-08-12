<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { PlusIcon, TableCellsIcon } from '@heroicons/vue/24/outline'
import { adminService } from '../../services/adminService'
import TableCard from '../../components/staff/TableCard.vue'
import Modal from '../../components/ui/Modal.vue'
import ConfirmDialog from '../../components/ui/ConfirmDialog.vue'
import SkeletonLoader from '../../components/ui/SkeletonLoader.vue'
import EmptyState from '../../components/ui/EmptyState.vue'

const toast = useToast()

const tables = ref([])
const loading = ref(true)

const modalOpen = ref(false)
const editing = ref(null)
const form = ref({ table_number: '', capacity: 2, status: 'available' })
const saving = ref(false)

const confirmOpen = ref(false)
const deleteTarget = ref(null)
const deleting = ref(false)

async function load() {
  loading.value = true
  try {
    const res = await adminService.tables.list()
    tables.value = res.data
  } catch (e) {
    toast.error(e.message)
  } finally {
    loading.value = false
  }
}
onMounted(load)

function openCreate() {
  editing.value = null
  form.value = { table_number: '', capacity: 2, status: 'available' }
  modalOpen.value = true
}

function openEdit(table) {
  editing.value = table
  form.value = { table_number: table.table_number, capacity: table.capacity, status: table.status }
  modalOpen.value = true
}

async function save() {
  saving.value = true
  try {
    if (editing.value) {
      await adminService.tables.update(editing.value.id, form.value)
      toast.success('Table updated')
    } else {
      await adminService.tables.create(form.value)
      toast.success('Table created')
    }
    modalOpen.value = false
    await load()
  } catch (e) {
    toast.error(e.message)
  } finally {
    saving.value = false
  }
}

function confirmDelete(table) {
  deleteTarget.value = table
  confirmOpen.value = true
}

async function doDelete() {
  deleting.value = true
  try {
    await adminService.tables.remove(deleteTarget.value.id)
    toast.success('Table deleted')
    confirmOpen.value = false
    await load()
  } catch (e) {
    toast.error(e.message)
  } finally {
    deleting.value = false
  }
}

async function regenerateQr(table) {
  try {
    await adminService.tables.regenerateQr(table.id)
    toast.success('QR code regenerated')
    await load()
  } catch (e) {
    toast.error(e.message)
  }
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Tables</h1>
      <button
        class="flex items-center gap-1.5 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500"
        @click="openCreate"
      >
        <PlusIcon class="h-4 w-4" /> Add Table
      </button>
    </div>

    <div v-if="loading" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
      <SkeletonLoader variant="card" :count="8" />
    </div>
    <EmptyState v-else-if="tables.length === 0" :icon="TableCellsIcon" title="No tables yet" message="Add your first table to generate a QR code." />
    <div v-else class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
      <TableCard
        v-for="table in tables"
        :key="table.id"
        :table="table"
        @edit="openEdit"
        @delete="confirmDelete"
        @regenerate-qr="regenerateQr"
      />
    </div>

    <Modal v-model="modalOpen" :title="editing ? 'Edit Table' : 'Add Table'" size="sm">
      <form class="flex flex-col gap-4" @submit.prevent="save">
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Table Number</label>
          <input
            v-model="form.table_number"
            type="text"
            required
            placeholder="T01"
            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900"
          />
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Capacity</label>
          <input
            v-model.number="form.capacity"
            type="number"
            min="1"
            required
            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900"
          />
        </div>
        <div v-if="editing">
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Status</label>
          <select
            v-model="form.status"
            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900"
          >
            <option value="available">Available</option>
            <option value="occupied">Occupied</option>
            <option value="reserved">Reserved</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <button
          type="submit"
          :disabled="saving"
          class="mt-2 w-full rounded-xl bg-primary-600 py-2.5 text-sm font-semibold text-white hover:bg-primary-500 disabled:opacity-50"
        >
          {{ saving ? 'Saving…' : 'Save Table' }}
        </button>
      </form>
    </Modal>

    <ConfirmDialog
      v-model="confirmOpen"
      title="Delete table?"
      :message="`This will permanently remove table ${deleteTarget?.table_number}.`"
      danger
      :loading="deleting"
      @confirm="doDelete"
    />
  </div>
</template>
