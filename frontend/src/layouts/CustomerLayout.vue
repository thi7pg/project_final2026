<script setup>
import { ref } from 'vue'
import CustomerNavbar from '../components/customer/CustomerNavbar.vue'
import CartDrawer from '../components/customer/CartDrawer.vue'
import Footer from '../components/customer/Footer.vue'

const cartOpen = ref(false)
</script>

<template>
  <div class="flex min-h-screen flex-col bg-surface dark:bg-slate-950">
    <CustomerNavbar @open-cart="cartOpen = true" />
    <main class="mx-auto w-full max-w-5xl flex-1 px-4 py-6">
      <RouterView />
    </main>
    <Footer />
    <CartDrawer v-model="cartOpen" />
  </div>
</template>
