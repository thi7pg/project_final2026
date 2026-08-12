<script setup></script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-primary-600 via-primary-700 to-slate-900 p-4">
    <div class="w-full max-w-md">
      <div class="mb-8 flex flex-col items-center gap-2 text-center text-white">
        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 text-2xl font-bold backdrop-blur">
          Q
        </div>
        <h1 class="text-xl font-bold">QR Order Staff Portal</h1>
        <p class="text-sm text-white/70">Sign in to manage orders, kitchen, and payments.</p>
      </div>
      <div class="rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900 sm:p-8">
        <RouterView />
      </div>
    </div>
  </div>
</template>
