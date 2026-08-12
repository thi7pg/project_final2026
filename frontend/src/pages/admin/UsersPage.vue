<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { PlusIcon, PencilIcon, TrashIcon, UsersIcon } from '@heroicons/vue/24/outline'
import { adminService } from '../../services/adminService'
import Modal from '../../components/ui/Modal.vue'
import ConfirmDialog from '../../components/ui/ConfirmDialog.vue'
import SkeletonLoader from '../../components/ui/SkeletonLoader.vue'
import EmptyState from '../../components/ui/EmptyState.vue'

const toast = useToast()

const users = ref([])
const loading = ref(true)

const modalOpen = ref(false)
const editing = ref(null)
const form = ref({ name: '', email: '', password: '', role: 'kitchen', is_active: true })
const saving = ref(false)

const confirmOpen = ref(false)
const deleteTarget = ref(null)
const deleting = ref(false)

async function load() {
  loading.value = true
  try {
    const res = await adminService.users.list()
    users.value = res.data
  } catch (e) {
    toast.error(e.message)
  } finally {
    loading.value = false
  }
}
onMounted(load)

function openCreate() {
  editing.value = null
  form.value = { name: '', email: '', password: '', role: 'kitchen', is_active: true }
  modalOpen.value = true
}

function openEdit(user) {
  editing.value = user
  form.value = { name: user.name, email: user.email, password: '', role: user.role, is_active: user.is_active }
  modalOpen.value = true
}

async function save() {
  saving.value = true
  try {
    if (editing.value) {
      const payload = { ...form.value }
      if (!payload.password) delete payload.password
      await adminService.users.update(editing.value.id, payload)
      toast.success('User updated')
    } else {
      await adminService.users.create(form.value)
      toast.success('User created')
    }
    modalOpen.value = false
    await load()
  } catch (e) {
    toast.error(e.message)
  } finally {
    saving.value = false
  }
}

function confirmDelete(user) {
  deleteTarget.value = user
  confirmOpen.value = true
}

async function doDelete() {
  deleting.value = true
  try {
    await adminService.users.remove(deleteTarget.value.id)
    toast.success('User removed')
    confirmOpen.value = false
    await load()
  } catch (e) {
    toast.error(e.message)
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Staff Users</h1>
      <button
        class="flex items-center gap-1.5 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500"
        @click="openCreate"
      >
        <PlusIcon class="h-4 w-4" /> Add Staff
      </button>
    </div>

    <div v-if="loading" class="flex flex-col gap-3">
      <SkeletonLoader variant="line" :count="4" />
    </div>
    <EmptyState v-else-if="users.length === 0" :icon="UsersIcon" title="No staff users yet" />
    <div v-else class="card divide-y divide-slate-100 dark:divide-slate-800">
      <div v-for="user in users" :key="user.id" class="flex items-center justify-between gap-3 px-4 py-3">
        <div class="flex items-center gap-3">
          <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-50 text-sm font-semibold text-primary-600 dark:bg-primary-500/10">
            {{ user.name.charAt(0).toUpperCase() }}
          </span>
          <div>
            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ user.name }}</p>
            <p class="text-xs text-slate-400">{{ user.email }}</p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium capitalize text-slate-600 dark:bg-slate-800 dark:text-slate-300">
            {{ user.role }}
          </span>
          <button class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" @click="openEdit(user)">
            <PencilIcon class="h-4 w-4" />
          </button>
          <button class="rounded-lg p-2 text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-500/10" @click="confirmDelete(user)">
            <TrashIcon class="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>

    <Modal v-model="modalOpen" :title="editing ? 'Edit Staff' : 'Add Staff'" size="sm">
      <form class="flex flex-col gap-4" @submit.prevent="save">
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Name</label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900"
          />
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900"
          />
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
            Password <span v-if="editing" class="text-slate-400">(leave blank to keep)</span>
          </label>
          <input
            v-model="form.password"
            type="password"
            :required="!editing"
            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900"
          />
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Role</label>
          <select
            v-model="form.role"
            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900"
          >
            <option value="admin">Admin</option>
            <option value="kitchen">Kitchen</option>
            <option value="cashier">Cashier</option>
          </select>
        </div>
        <div class="flex items-center justify-between">
          <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Active</label>
          <input v-model="form.is_active" type="checkbox" class="h-5 w-5 rounded text-primary-600 focus:ring-primary-500" />
        </div>
        <button
          type="submit"
          :disabled="saving"
          class="mt-2 w-full rounded-xl bg-primary-600 py-2.5 text-sm font-semibold text-white hover:bg-primary-500 disabled:opacity-50"
        >
          {{ saving ? 'Saving…' : 'Save Staff' }}
        </button>
      </form>
    </Modal>

    <ConfirmDialog
      v-model="confirmOpen"
      title="Remove staff user?"
      :message="`This will remove ${deleteTarget?.name}'s access.`"
      danger
      :loading="deleting"
      @confirm="doDelete"
    />
  </div>
</template>
