<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import AppLayout from '@/layouts/AppLayout.vue'
import EmptyLayout from '@/layouts/EmptyLayout.vue'

const route = useRoute()
const layout = computed(() => route.meta.layout || 'app')
</script>

<template>
  <AppLayout v-if="layout === 'app'" />
  <EmptyLayout v-else-if="layout === 'empty'" />
  <AppLayout v-else />
</template>