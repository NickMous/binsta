<script setup lang="ts">
import Navbar from '@/components/Navbar.vue'

const handleToggleTheme = () => {
  console.log('Toggle theme clicked')
}

const handleOpenNotifications = () => {
  console.log('Open notifications clicked')
}

const handleOpenProfile = () => {
  console.log('Open profile clicked')
}
</script>

<template>
  <div class="min-h-screen bg-background">
    <Navbar 
      @toggle-theme="handleToggleTheme"
      @open-notifications="handleOpenNotifications"
      @open-profile="handleOpenProfile"
    />
    
    <main class="container max-w-screen-2xl mx-auto px-4 py-6">
      <slot></slot>
    </main>
  </div>
</template>

<style scoped>

</style>