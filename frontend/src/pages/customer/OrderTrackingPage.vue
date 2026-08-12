<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { CheckCircleIcon, XCircleIcon, FaceFrownIcon } from '@heroicons/vue/24/solid'
import { orderService } from '../../services/orderService'
import { useCartStore } from '../../stores/cart'
import { formatCurrency, formatTime } from '../../utils/format'
import LoadingSpinner from '../../components/ui/LoadingSpinner.vue'
import EmptyState from '../../components/ui/EmptyState.vue'

const route = useRoute()
const cart = useCartStore()

const order = ref(null)
const loading = ref(true)
const error = ref('')
let pollTimer = null

const steps = [
  { key: 'pending', label: 'Pending' },
  { key: 'confirmed', label: 'Confirmed' },
  { key: 'preparing', label: 'Preparing' },
  { key: 'ready', label: 'Ready' },
  { key: 'completed', label: 'Completed' },
]

const currency = computed(() => cart.restaurant?.currency ?? 'USD')
const currentStepIndex = computed(() => steps.findIndex((s) => s.key === order.value?.status))
const isCancelled = computed(() => order.value?.status === 'cancelled')

async function load() {
  try {
    const res = await orderService.track(route.params.orderNumber)
    order.value = res.data
    error.value = ''
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  load()
  pollTimer = setInterval(load, 10000)
})
onUnmounted(() => clearInterval(pollTimer))
</script>

<template>
  <div v-if="loading" class="flex justify-center py-20"><LoadingSpinner size="lg" /></div>

  <EmptyState
    v-else-if="error"
    :icon="FaceFrownIcon"
    title="Order not found"
    :message="error"
  />

  <div v-else-if="order" class="flex flex-col gap-6">
    <div class="card p-5 text-center">
      <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Order Number</p>
      <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">#{{ order.order_number }}</p>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Table {{ order.table }}</p>
    </div>

    <div v-if="isCancelled" class="card flex flex-col items-center gap-2 p-8 text-center">
      <XCircleIcon class="h-14 w-14 text-danger-500" />
      <p class="text-lg font-bold text-slate-900 dark:text-slate-100">Order Cancelled</p>
      <p class="text-sm text-slate-500 dark:text-slate-400">Please speak with a staff member if you have questions.</p>
    </div>

    <div v-else class="card p-6">
      <div class="relative flex justify-between">
        <div class="absolute left-0 right-0 top-4 h-0.5 bg-slate-200 dark:bg-slate-800" />
        <div
          class="absolute left-0 top-4 h-0.5 bg-primary-600 transition-all duration-500"
          :style="{ width: `${(currentStepIndex / (steps.length - 1)) * 100}%` }"
        />
        <div v-for="(step, index) in steps" :key="step.key" class="relative z-10 flex flex-col items-center gap-2">
          <div
            class="flex h-8 w-8 items-center justify-center rounded-full border-2 text-xs font-bold transition"
            :class="index <= currentStepIndex
              ? 'border-primary-600 bg-primary-600 text-white'
              : 'border-slate-300 bg-white text-slate-400 dark:border-slate-700 dark:bg-slate-900'"
          >
            <CheckCircleIcon v-if="index < currentStepIndex" class="h-5 w-5" />
            <span v-else>{{ index + 1 }}</span>
          </div>
          <span class="w-16 text-center text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ step.label }}</span>
        </div>
      </div>

      <p class="mt-8 text-center text-sm text-slate-500 dark:text-slate-400">
        <template v-if="order.status === 'pending'">We've received your order and will confirm shortly.</template>
        <template v-else-if="order.status === 'confirmed'">Your order has been confirmed.</template>
        <template v-else-if="order.status === 'preparing'">Our kitchen is preparing your food.</template>
        <template v-else-if="order.status === 'ready'">Your order is ready to be served!</template>
        <template v-else-if="order.status === 'completed'">Enjoy your meal! Order completed.</template>
      </p>
    </div>

    <div class="card p-5">
      <h2 class="mb-3 font-semibold text-slate-900 dark:text-slate-100">Order Items</h2>
      <ul class="flex flex-col gap-2 text-sm">
        <li v-for="item in order.items" :key="item.id" class="flex justify-between text-slate-600 dark:text-slate-300">
          <span>{{ item.quantity }}&times; {{ item.product_name }}</span>
          <span>{{ formatCurrency(item.subtotal, currency) }}</span>
        </li>
      </ul>
      <div class="mt-4 flex justify-between border-t border-slate-100 pt-3 text-base font-bold text-slate-900 dark:border-slate-800 dark:text-slate-100">
        <span>Total</span>
        <span>{{ formatCurrency(order.total_amount, currency) }}</span>
      </div>
    </div>

    <p class="text-center text-xs text-slate-400">Placed at {{ formatTime(order.created_at) }} &middot; updates automatically</p>
  </div>
</template>
