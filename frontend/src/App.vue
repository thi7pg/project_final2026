<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth'

const auth = useAuthStore()

onMounted(async () => {
  if (!auth.token) return
  try {
    await auth.fetchMe()
  } catch {
    // stored token is no longer valid; auth store stays logged out
  }
})
</script>

<template>
  <RouterView />
</template>
