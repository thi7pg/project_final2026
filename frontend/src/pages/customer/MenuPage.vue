<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { MagnifyingGlassIcon, MapPinIcon, PhoneIcon, FaceFrownIcon } from '@heroicons/vue/24/outline'
import { menuService } from '../../services/menuService'
import { useCartStore } from '../../stores/cart'
import ProductCard from '../../components/customer/ProductCard.vue'
import CategoryTabs from '../../components/customer/CategoryTabs.vue'
import SkeletonLoader from '../../components/ui/SkeletonLoader.vue'
import EmptyState from '../../components/ui/EmptyState.vue'

const route = useRoute()
const cart = useCartStore()

const menu = ref(null)
const loading = ref(true)
const error = ref('')
const search = ref('')
const activeCategory = ref('all')

async function loadMenu() {
  loading.value = true
  error.value = ''
  try {
    const res = await menuService.getMenu(route.params.qrToken)
    menu.value = res.data
    cart.setContext({
      qrToken: route.params.qrToken,
      table: menu.value.table,
      restaurant: menu.value.restaurant,
      categories: menu.value.categories,
    })
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}

onMounted(loadMenu)
watch(() => route.params.qrToken, loadMenu)

const filteredProducts = computed(() => {
  if (!menu.value) return []
  let categories = menu.value.categories
  if (activeCategory.value !== 'all') {
    categories = categories.filter((c) => c.id === activeCategory.value)
  }
  let products = categories.flatMap((c) => c.products ?? [])
  if (search.value.trim()) {
    const term = search.value.trim().toLowerCase()
    products = products.filter((p) => p.name.toLowerCase().includes(term))
  }
  return products
})
</script>

<template>
  <div v-if="loading" class="flex flex-col gap-6">
    <div class="h-24 animate-pulse rounded-2xl bg-slate-200 dark:bg-slate-800" />
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
      <SkeletonLoader variant="card" :count="8" />
    </div>
  </div>

  <EmptyState
    v-else-if="error"
    :icon="FaceFrownIcon"
    title="Table not found"
    :message="error"
  />

  <div v-else class="flex flex-col gap-6">
    <section class="card overflow-hidden">
      <div class="bg-gradient-to-r from-primary-600 to-primary-700 p-5 text-white">
        <h1 class="text-xl font-bold">{{ menu.restaurant.name }}</h1>
        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-white/80">
          <span v-if="menu.restaurant.address" class="flex items-center gap-1">
            <MapPinIcon class="h-4 w-4" /> {{ menu.restaurant.address }}
          </span>
          <span v-if="menu.restaurant.phone" class="flex items-center gap-1">
            <PhoneIcon class="h-4 w-4" /> {{ menu.restaurant.phone }}
          </span>
        </div>
      </div>
      <div class="flex items-center justify-between px-5 py-3 text-sm text-slate-500 dark:text-slate-400">
        <span>Table <strong class="text-slate-900 dark:text-slate-100">{{ menu.table.table_number }}</strong></span>
        <span>Seats {{ menu.table.capacity }}</span>
      </div>
    </section>

    <div class="relative">
      <MagnifyingGlassIcon class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
      <input
        v-model="search"
        type="search"
        placeholder="Search dishes…"
        class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900"
      />
    </div>

    <CategoryTabs v-model="activeCategory" :categories="menu.categories" />

    <EmptyState
      v-if="filteredProducts.length === 0"
      title="No dishes found"
      message="Try a different search term or category."
    />
    <div v-else class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
      <ProductCard
        v-for="product in filteredProducts"
        :key="product.id"
        :product="product"
        :currency="menu.restaurant.currency"
      />
    </div>
  </div>
</template>
