<script setup>
import { XMarkIcon, MinusIcon, PlusIcon, TrashIcon, ShoppingBagIcon } from '@heroicons/vue/24/outline'
import { useRouter } from 'vue-router'
import { useCartStore } from '../../stores/cart'
import { formatCurrency } from '../../utils/format'
import EmptyState from '../ui/EmptyState.vue'

const props = defineProps({ modelValue: { type: Boolean, default: false } })
const emit = defineEmits(['update:modelValue'])

const cart = useCartStore()
const router = useRouter()

function close() {
  emit('update:modelValue', false)
}

function goCheckout() {
  close()
  router.push({ name: 'checkout' })
}

const currency = () => cart.restaurant?.currency ?? 'USD'
</script>

<template>
  <Teleport to="body">
    <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" leave-active-class="transition duration-150" leave-to-class="opacity-0">
      <div v-if="modelValue" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm" @click="close" />
    </Transition>

    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="translate-x-full"
      leave-active-class="transition duration-200 ease-in"
      leave-to-class="translate-x-full"
    >
      <aside
        v-if="modelValue"
        class="fixed inset-y-0 right-0 z-50 flex w-full max-w-sm flex-col bg-white shadow-2xl dark:bg-slate-900"
      >
        <header class="flex items-center justify-between border-b border-slate-100 p-4 dark:border-slate-800">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Your Order</h2>
          <button class="rounded-full p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" @click="close">
            <XMarkIcon class="h-5 w-5" />
          </button>
        </header>

        <div class="flex-1 overflow-y-auto p-4">
          <EmptyState v-if="cart.items.length === 0" :icon="ShoppingBagIcon" title="Your cart is empty" message="Add items from the menu to get started." />

          <ul v-else class="flex flex-col gap-4">
            <li v-for="(item, index) in cart.items" :key="index" class="flex gap-3">
              <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                <img v-if="item.image" :src="item.image" :alt="item.name" class="h-full w-full object-cover" />
              </div>
              <div class="flex flex-1 flex-col">
                <div class="flex items-start justify-between gap-2">
                  <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ item.name }}</p>
                  <button class="text-slate-400 hover:text-danger-600" @click="cart.removeItem(index)">
                    <TrashIcon class="h-4 w-4" />
                  </button>
                </div>
                <p v-if="item.notes" class="mt-0.5 text-xs italic text-slate-400">"{{ item.notes }}"</p>
                <div class="mt-2 flex items-center justify-between">
                  <div class="flex items-center gap-2 rounded-full border border-slate-200 px-1.5 py-0.5 dark:border-slate-700">
                    <button class="p-1 text-slate-500 hover:text-primary-600" @click="cart.updateQuantity(index, item.quantity - 1)">
                      <MinusIcon class="h-3.5 w-3.5" />
                    </button>
                    <span class="w-4 text-center text-sm font-medium">{{ item.quantity }}</span>
                    <button class="p-1 text-slate-500 hover:text-primary-600" @click="cart.updateQuantity(index, item.quantity + 1)">
                      <PlusIcon class="h-3.5 w-3.5" />
                    </button>
                  </div>
                  <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                    {{ formatCurrency(item.price * item.quantity, currency()) }}
                  </span>
                </div>
              </div>
            </li>
          </ul>
        </div>

        <footer v-if="cart.items.length > 0" class="space-y-3 border-t border-slate-100 p-4 dark:border-slate-800">
          <div class="space-y-1 text-sm">
            <div class="flex justify-between text-slate-500 dark:text-slate-400">
              <span>Subtotal</span>
              <span>{{ formatCurrency(cart.subtotal, currency()) }}</span>
            </div>
            <div v-if="cart.taxAmount > 0" class="flex justify-between text-slate-500 dark:text-slate-400">
              <span>Tax</span>
              <span>{{ formatCurrency(cart.taxAmount, currency()) }}</span>
            </div>
            <div v-if="cart.serviceChargeAmount > 0" class="flex justify-between text-slate-500 dark:text-slate-400">
              <span>Service charge</span>
              <span>{{ formatCurrency(cart.serviceChargeAmount, currency()) }}</span>
            </div>
            <div class="flex justify-between text-base font-bold text-slate-900 dark:text-slate-100">
              <span>Total</span>
              <span>{{ formatCurrency(cart.total, currency()) }}</span>
            </div>
          </div>
          <button
            class="w-full rounded-xl bg-primary-600 py-3 text-sm font-semibold text-white transition hover:bg-primary-500"
            @click="goCheckout"
          >
            Proceed to Checkout
          </button>
        </footer>
      </aside>
    </Transition>
  </Teleport>
</template>
