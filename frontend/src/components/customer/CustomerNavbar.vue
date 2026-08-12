<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ArrowLeftIcon, ShoppingBagIcon } from '@heroicons/vue/24/outline'
import { useCartStore } from '../../stores/cart'

const emit = defineEmits(['open-cart'])
const route = useRoute()
const router = useRouter()
const cart = useCartStore()

const showBack = computed(() => route.name !== 'landing' && route.name !== 'menu')
const restaurantName = computed(() => cart.restaurant?.name ?? 'Restaurant')
const tableLabel = computed(() => (cart.table ? `Table ${cart.table.table_number}` : ''))
</script>

<template>
  <header class="glass sticky top-0 z-30 border-b border-slate-200/70 dark:border-slate-800">
    <div class="mx-auto flex h-16 max-w-5xl items-center justify-between px-4">
      <div class="flex items-center gap-3">
        <button
          v-if="showBack"
          class="rounded-full p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
          @click="router.back()"
        >
          <ArrowLeftIcon class="h-5 w-5" />
        </button>
        <div>
          <p class="text-sm font-bold leading-tight text-slate-900 dark:text-slate-100">{{ restaurantName }}</p>
          <p v-if="tableLabel" class="text-xs text-slate-500 dark:text-slate-400">{{ tableLabel }}</p>
        </div>
      </div>

      <button
        class="relative flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
        @click="emit('open-cart')"
      >
        <ShoppingBagIcon class="h-5 w-5" />
        <span
          v-if="cart.itemCount > 0"
          class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-primary-600 px-1 text-[11px] font-bold text-white"
        >
          {{ cart.itemCount }}
        </span>
      </button>
    </div>
  </header>
</template>
