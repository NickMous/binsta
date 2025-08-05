<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import AppLayout from '@/layouts/AppLayout.vue'
import EmptyLayout from '@/layouts/EmptyLayout.vue'
import { useUserStore } from '@/stores/UserStore'

const route = useRoute()
const userStore = useUserStore()
const layout = computed(() => route.meta.layout || 'app')

// Load persisted user data when app starts
onMounted(() => {
  userStore.loadPersistedUser()
})
</script>

<template>
  <AppLayout v-if="layout === 'app'" />
  <EmptyLayout v-else-if="layout === 'empty'" />
  <AppLayout v-else />
</template>