<script setup>
defineProps({
  categories: { type: Array, required: true },
  modelValue: { type: [Number, String], default: 'all' },
})
defineEmits(['update:modelValue'])
</script>

<template>
  <div class="scrollbar-none flex gap-2 overflow-x-auto pb-1">
    <button
      class="shrink-0 rounded-full px-4 py-2 text-sm font-medium transition"
      :class="modelValue === 'all'
        ? 'bg-primary-600 text-white shadow-sm'
        : 'bg-white text-slate-600 hover:bg-slate-100 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800'"
      @click="$emit('update:modelValue', 'all')"
    >
      All
    </button>
    <button
      v-for="category in categories"
      :key="category.id"
      class="shrink-0 rounded-full px-4 py-2 text-sm font-medium transition"
      :class="modelValue === category.id
        ? 'bg-primary-600 text-white shadow-sm'
        : 'bg-white text-slate-600 hover:bg-slate-100 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800'"
      @click="$emit('update:modelValue', category.id)"
    >
      {{ category.name }}
    </button>
  </div>
</template>
