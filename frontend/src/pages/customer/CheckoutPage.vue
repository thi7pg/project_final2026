<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { orderService } from '../../services/orderService'
import { useCartStore } from '../../stores/cart'
import { formatCurrency } from '../../utils/format'

const cart = useCartStore()
const router = useRouter()
const toast = useToast()

const customerName = ref('')
const customerPhone = ref('')
const notes = ref('')
const submitting = ref(false)
const error = ref('')

const currency = computed(() => cart.restaurant?.currency ?? 'USD')

onMounted(() => {
  if (cart.items.length === 0) {
    router.replace({ name: 'cart' })
  }
})

async function submitOrder() {
  if (!customerName.value.trim()) {
    error.value = 'Please enter your name.'
    return
  }
  submitting.value = true
  error.value = ''
  try {
    const payload = {
      qr_token: cart.qrToken,
      customer_name: customerName.value.trim(),
      customer_phone: customerPhone.value.trim() || undefined,
      notes: notes.value.trim() || undefined,
      items: cart.items.map((item) => ({
        product_id: item.productId,
        quantity: item.quantity,
        notes: item.notes || undefined,
      })),
    }
    const res = await orderService.place(payload)
    const orderNumber = res.data.order_number
    cart.clear()
    toast.success('Order placed! Track your food below.')
    router.push({ name: 'order-tracking', params: { orderNumber } })
  } catch (e) {
    error.value = e.message
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Checkout</h1>

    <form class="card flex flex-col gap-4 p-5" @submit.prevent="submitOrder">
      <div>
        <label for="name" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
          Your Name
        </label>
        <input
          id="name"
          v-model="customerName"
          type="text"
          required
          placeholder="John Doe"
          class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900"
        />
      </div>

      <div>
        <label for="phone" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
          Phone Number <span class="text-slate-400">(optional)</span>
        </label>
        <input
          id="phone"
          v-model="customerPhone"
          type="tel"
          placeholder="0123456789"
          class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900"
        />
      </div>

      <div>
        <label for="notes" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
          Special Request <span class="text-slate-400">(optional)</span>
        </label>
        <textarea
          id="notes"
          v-model="notes"
          rows="3"
          placeholder="Anything the kitchen should know?"
          class="w-full resize-none rounded-xl border border-slate-200 p-3 text-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900"
        />
      </div>
    </form>

    <div class="card p-5">
      <h2 class="mb-3 font-semibold text-slate-900 dark:text-slate-100">Order Summary</h2>
      <ul class="flex flex-col gap-2 text-sm">
        <li v-for="(item, index) in cart.items" :key="index" class="flex justify-between text-slate-600 dark:text-slate-300">
          <span>{{ item.quantity }}&times; {{ item.name }}</span>
          <span>{{ formatCurrency(item.price * item.quantity, currency) }}</span>
        </li>
      </ul>
      <div class="mt-4 space-y-1.5 border-t border-slate-100 pt-3 text-sm dark:border-slate-800">
        <div class="flex justify-between text-slate-500 dark:text-slate-400">
          <span>Subtotal</span>
          <span>{{ formatCurrency(cart.subtotal, currency) }}</span>
        </div>
        <div v-if="cart.taxAmount > 0" class="flex justify-between text-slate-500 dark:text-slate-400">
          <span>Tax</span>
          <span>{{ formatCurrency(cart.taxAmount, currency) }}</span>
        </div>
        <div v-if="cart.serviceChargeAmount > 0" class="flex justify-between text-slate-500 dark:text-slate-400">
          <span>Service charge</span>
          <span>{{ formatCurrency(cart.serviceChargeAmount, currency) }}</span>
        </div>
        <div class="flex justify-between text-base font-bold text-slate-900 dark:text-slate-100">
          <span>Total</span>
          <span>{{ formatCurrency(cart.total, currency) }}</span>
        </div>
      </div>
    </div>

    <p v-if="error" class="text-sm font-medium text-danger-600">{{ error }}</p>

    <button
      class="w-full rounded-xl bg-primary-600 py-3.5 text-sm font-semibold text-white transition hover:bg-primary-500 disabled:opacity-50"
      :disabled="submitting"
      @click="submitOrder"
    >
      {{ submitting ? 'Placing Order…' : 'Confirm Order' }}
    </button>
  </div>
</template>
