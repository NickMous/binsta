<script setup lang="ts">
import {useBreadcrumbStore} from "@/stores/BreadcrumbStore.ts";

const breadcrumbStore = useBreadcrumbStore();
breadcrumbStore.replaceBreadcrumbs([
  { name: 'Home', path: '/' },
])
</script>

<template>
  <div>
    <h1>Welcome to Binsta</h1>
  </div>
</template>

<style scoped>

</style>