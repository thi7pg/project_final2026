<script setup>
import { ClockIcon, PlusIcon } from '@heroicons/vue/24/outline'
import { useRouter } from 'vue-router'
import { formatCurrency } from '../../utils/format'
import { useCartStore } from '../../stores/cart'
import { useToast } from 'vue-toastification'

const props = defineProps({
  product: { type: Object, required: true },
  currency: { type: String, default: 'USD' },
})

const router = useRouter()
const cart = useCartStore()
const toast = useToast()

function openDetail() {
  router.push({ name: 'product-detail', params: { id: props.product.id } })
}

function quickAdd(event) {
  event.stopPropagation()
  if (!props.product.available) return
  cart.addItem(props.product, 1)
  toast.success(`${props.product.name} added to cart`)
}
</script>

<template>
  <article
    class="card group flex cursor-pointer flex-col overflow-hidden transition hover:-translate-y-0.5 hover:shadow-md"
    @click="openDetail"
  >
    <div class="relative aspect-square w-full overflow-hidden bg-slate-100 dark:bg-slate-800">
      <img
        v-if="product.image"
        :src="product.image"
        :alt="product.name"
        class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
        loading="lazy"
      />
      <div v-else class="flex h-full w-full items-center justify-center text-slate-300 dark:text-slate-600">
        <span class="text-3xl">🍽️</span>
      </div>
      <span
        v-if="!product.available"
        class="absolute inset-0 flex items-center justify-center bg-slate-900/50 text-sm font-semibold text-white"
      >
        Unavailable
      </span>
    </div>

    <div class="flex flex-1 flex-col gap-1.5 p-3">
      <h3 class="line-clamp-1 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ product.name }}</h3>
      <p v-if="product.description" class="line-clamp-2 text-xs text-slate-500 dark:text-slate-400">
        {{ product.description }}
      </p>

      <div class="mt-auto flex items-center justify-between pt-2">
        <div class="flex flex-col">
          <span class="text-sm font-bold text-primary-600 dark:text-primary-400">
            {{ formatCurrency(product.price, currency) }}
          </span>
          <span v-if="product.preparation_time" class="flex items-center gap-1 text-[11px] text-slate-400">
            <ClockIcon class="h-3 w-3" /> {{ product.preparation_time }} min
          </span>
        </div>

        <button
          class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-600 text-white transition hover:bg-primary-500 disabled:cursor-not-allowed disabled:bg-slate-300 dark:disabled:bg-slate-700"
          :disabled="!product.available"
          @click="quickAdd"
        >
          <PlusIcon class="h-4 w-4" />
        </button>
      </div>
    </div>
  </article>
</template>
