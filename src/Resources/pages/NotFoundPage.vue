<script setup lang="ts">
import {useBreadcrumbStore} from "@/stores/BreadcrumbStore.ts";
import {Button} from "@/components/ui/button";

const breadcrumbStore = useBreadcrumbStore();
breadcrumbStore.replaceBreadcrumbs([
  { name: 'Page Not Found', path: '/404' },
])
</script>

<template>
  <div class="flex flex-col items-center justify-center min-h-[50vh] text-center">
    <div class="mb-8">
      <h1 class="text-6xl font-bold text-gray-900 dark:text-gray-100 mb-4">404</h1>
      <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-2">Page Not Found</h2>
      <p class="text-gray-600 dark:text-gray-400 max-w-md">
        Sorry, we couldn't find the page you're looking for. It might have been moved, deleted, or you entered the wrong URL.
      </p>
    </div>
    
    <div class="space-y-4">
      <Button as-child>
        <RouterLink to="/">
          Go Back Home
        </RouterLink>
      </Button>
      
      <Button 
        variant="ghost" 
        class="block mx-auto"
        @click="$router.go(-1)"
      >
        Or go back to previous page
      </Button>
    </div>
  </div>
</template>

<style scoped>
</style>