<script setup lang="ts">
import { NavigationMenuLink } from '@/components/ui/navigation-menu'
</script>

<template>
  <NavigationMenuLink as-child>
    <router-link 
      to="/"
      class="group inline-flex h-9 w-max items-center justify-center rounded-md bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground focus:outline-none disabled:pointer-events-none disabled:opacity-50 data-[active]:bg-accent/50 data-[state=open]:bg-accent/50"
    >
      <div class="flex items-center space-x-2">
        <div class="h-6 w-6 rounded-full bg-gradient-to-br from-primary to-primary/60 flex items-center justify-center">
          <span class="text-xs font-bold text-primary-foreground">B</span>
        </div>
        <span class="font-semibold">Binsta</span>
      </div>
    </router-link>
  </NavigationMenuLink>
</template>