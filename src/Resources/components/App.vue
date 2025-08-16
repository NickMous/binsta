<script setup lang="ts">
import {computed, onMounted} from 'vue'
import {useRoute} from 'vue-router'
import AppLayout from '@/layouts/AppLayout.vue'
import EmptyLayout from '@/layouts/EmptyLayout.vue'
import {useUserStore} from '@/stores/UserStore.ts'

const props = defineProps<{
  user: string
}>()

const route = useRoute()
const userStore = useUserStore()
const layout = computed(() => route.meta.layout || 'app')

// Initialize user data when app starts
onMounted(async () => {
  const userId = parseInt(props.user);
  
  if (userId === 0) {
    userStore.clearUser()
  } else {
    await userStore.initializeUser(userId)
  }
})
</script>

<template>
  <AppLayout v-if="layout === 'app'"/>
  <EmptyLayout v-else-if="layout === 'empty'"/>
  <AppLayout v-else/>
</template>