<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { QrCodeIcon, SparklesIcon, ClockIcon, DevicePhoneMobileIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const manualOpen = ref(false)
const manualToken = ref('')

function goToMenu() {
  if (!manualToken.value.trim()) return
  router.push({ name: 'menu', params: { qrToken: manualToken.value.trim() } })
}
</script>

<template>
  <div class="flex flex-col items-center gap-10 py-10 text-center">
    <div class="flex flex-col items-center gap-4">
      <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-primary-600 text-3xl font-bold text-white shadow-lg shadow-primary-600/30">
        Q
      </div>
      <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100 sm:text-4xl">
        Welcome to the Table
      </h1>
      <p class="max-w-md text-slate-500 dark:text-slate-400">
        Scan the QR code on your table to browse our menu, order in seconds, and track your food in real time.
      </p>
    </div>

    <div class="grid w-full max-w-md gap-3 sm:grid-cols-3">
      <div class="card flex flex-col items-center gap-2 p-4">
        <SparklesIcon class="h-6 w-6 text-primary-600" />
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Fresh Menu</p>
      </div>
      <div class="card flex flex-col items-center gap-2 p-4">
        <ClockIcon class="h-6 w-6 text-primary-600" />
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Live Status</p>
      </div>
      <div class="card flex flex-col items-center gap-2 p-4">
        <DevicePhoneMobileIcon class="h-6 w-6 text-primary-600" />
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">No App Needed</p>
      </div>
    </div>

    <div class="flex flex-col items-center gap-3">
      <button
        class="flex items-center gap-2 rounded-full bg-primary-600 px-8 py-3.5 text-sm font-semibold text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-500"
        @click="manualOpen = !manualOpen"
      >
        <QrCodeIcon class="h-5 w-5" /> Scan QR to Start Ordering
      </button>

      <Transition enter-active-class="animate-fade-in">
        <form v-if="manualOpen" class="flex w-full max-w-xs gap-2" @submit.prevent="goToMenu">
          <input
            v-model="manualToken"
            type="text"
            placeholder="Enter table code"
            class="w-full rounded-full border border-slate-200 px-4 py-2 text-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900"
          />
          <button
            type="submit"
            class="shrink-0 rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900"
          >
            Go
          </button>
        </form>
      </Transition>
      <p v-if="manualOpen" class="text-xs text-slate-400">No camera? Enter the code printed under your table's QR.</p>
    </div>
  </div>
</template>
