<script setup lang="ts">
import { ref } from 'vue'

const hasNotifications = ref(true)
const isLoggedIn = ref(false) // TODO: Replace with actual auth state
</script>

<template>
  <nav class="flex items-center space-x-1">
    <button 
      class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 w-9"
      @click="$emit('toggle-theme')"
    >
      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
      </svg>
      <span class="sr-only">Toggle theme</span>
    </button>
    
    <button 
      class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 w-9 relative"
      @click="$emit('open-notifications')"
    >
      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 7H4l5-5v5zM15 7h5l-5-5v5zM9 17H4l5 5v-5z" />
      </svg>
      <span 
        v-if="hasNotifications" 
        class="absolute -top-1 -right-1 h-2 w-2 bg-red-500 rounded-full"
      ></span>
      <span class="sr-only">Notifications</span>
    </button>
    
    <div class="relative">
      <!-- User Profile Button (when logged in) -->
      <button 
        v-if="isLoggedIn"
        class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 w-9"
        @click="$emit('open-profile')"
      >
        <div class="h-6 w-6 rounded-full bg-gradient-to-br from-primary to-primary/60 flex items-center justify-center">
          <span class="text-xs font-bold text-primary-foreground">U</span>
        </div>
        <span class="sr-only">User profile</span>
      </button>

      <!-- Login Button (when not logged in) -->
      <button 
        v-else
        class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-3"
        @click="$emit('login')"
      >
        Log in
      </button>
    </div>
  </nav>
</template>