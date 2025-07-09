<script setup lang="ts">
import {useBreadcrumbStore} from "@/stores/BreadcrumbStore.ts";

const breadcrumbStore = useBreadcrumbStore();
breadcrumbStore.replaceBreadcrumbs([
  { name: 'Home', path: '/' },
])
</script>

<template>
</template>

<style scoped>

</style>