<script setup lang="ts">
import { ref } from 'vue'

const searchQuery = ref('')
</script>

<template>
  <div class="w-full flex-1 md:w-auto md:flex-none">
    <div class="relative">
      <input 
        v-model="searchQuery"
        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 md:w-[300px]"
        placeholder="Search..."
        type="search"
      />
      <svg class="absolute right-3 top-3 h-3 w-3 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 1 1-14 0 7 7 0 0 1 14 0z" />
      </svg>
    </div>
  </div>
</template>