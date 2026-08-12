<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { MinusIcon, PlusIcon, TrashIcon, ShoppingBagIcon } from '@heroicons/vue/24/outline'
import { useCartStore } from '../../stores/cart'
import { formatCurrency } from '../../utils/format'
import EmptyState from '../../components/ui/EmptyState.vue'

const cart = useCartStore()
const router = useRouter()

const currency = computed(() => cart.restaurant?.currency ?? 'USD')

function backToMenu() {
  router.push(cart.qrToken ? { name: 'menu', params: { qrToken: cart.qrToken } } : { name: 'landing' })
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Your Cart</h1>

    <EmptyState
      v-if="cart.items.length === 0"
      :icon="ShoppingBagIcon"
      title="Your cart is empty"
      message="Browse the menu and add some delicious items."
    >
      <button class="mt-2 rounded-full bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-500" @click="backToMenu">
        Browse Menu
      </button>
    </EmptyState>

    <template v-else>
      <ul class="flex flex-col gap-3">
        <li v-for="(item, index) in cart.items" :key="index" class="card flex gap-4 p-4">
          <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
            <img v-if="item.image" :src="item.image" :alt="item.name" class="h-full w-full object-cover" />
          </div>
          <div class="flex flex-1 flex-col">
            <div class="flex items-start justify-between gap-2">
              <p class="font-medium text-slate-900 dark:text-slate-100">{{ item.name }}</p>
              <button class="text-slate-400 hover:text-danger-600" @click="cart.removeItem(index)">
                <TrashIcon class="h-4 w-4" />
              </button>
            </div>
            <input
              v-model="item.notes"
              type="text"
              placeholder="Add a note…"
              class="mt-1 w-full max-w-xs rounded-lg border border-transparent bg-slate-50 px-2 py-1 text-xs text-slate-500 focus:border-primary-300 focus:bg-white focus:outline-none dark:bg-slate-800 dark:focus:bg-slate-900"
            />
            <div class="mt-3 flex items-center justify-between">
              <div class="flex items-center gap-2 rounded-full border border-slate-200 px-1.5 py-0.5 dark:border-slate-700">
                <button class="p-1.5 text-slate-500 hover:text-primary-600" @click="cart.updateQuantity(index, item.quantity - 1)">
                  <MinusIcon class="h-3.5 w-3.5" />
                </button>
                <span class="w-5 text-center text-sm font-medium">{{ item.quantity }}</span>
                <button class="p-1.5 text-slate-500 hover:text-primary-600" @click="cart.updateQuantity(index, item.quantity + 1)">
                  <PlusIcon class="h-3.5 w-3.5" />
                </button>
              </div>
              <span class="font-semibold text-slate-900 dark:text-slate-100">
                {{ formatCurrency(item.price * item.quantity, currency) }}
              </span>
            </div>
          </div>
        </li>
      </ul>

      <div class="card space-y-2 p-5">
        <div class="flex justify-between text-sm text-slate-500 dark:text-slate-400">
          <span>Subtotal</span>
          <span>{{ formatCurrency(cart.subtotal, currency) }}</span>
        </div>
        <div v-if="cart.taxAmount > 0" class="flex justify-between text-sm text-slate-500 dark:text-slate-400">
          <span>Tax</span>
          <span>{{ formatCurrency(cart.taxAmount, currency) }}</span>
        </div>
        <div v-if="cart.serviceChargeAmount > 0" class="flex justify-between text-sm text-slate-500 dark:text-slate-400">
          <span>Service charge</span>
          <span>{{ formatCurrency(cart.serviceChargeAmount, currency) }}</span>
        </div>
        <div class="flex justify-between border-t border-slate-100 pt-2 text-lg font-bold text-slate-900 dark:border-slate-800 dark:text-slate-100">
          <span>Total</span>
          <span>{{ formatCurrency(cart.total, currency) }}</span>
        </div>
      </div>

      <div class="flex gap-3">
        <button
          class="flex-1 rounded-xl border border-slate-200 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
          @click="backToMenu"
        >
          Add More Items
        </button>
        <button
          class="flex-1 rounded-xl bg-primary-600 py-3 text-sm font-semibold text-white hover:bg-primary-500"
          @click="router.push({ name: 'checkout' })"
        >
          Checkout
        </button>
      </div>
    </template>
  </div>
</template>
