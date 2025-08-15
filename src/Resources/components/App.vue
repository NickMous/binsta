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

// Load persisted user data when app starts
onMounted(async () => {
  const userId = parseInt(props.user);
  
  if (userId === 0) {
    userStore.clearUser()
  } else if (userStore.id !== userId) {
    // Either load persisted user or fetch new user data
    try {
      await userStore.fetchUser(userId)
    } catch (error) {
      console.error('Failed to fetch user:', error)
      userStore.clearUser()
    }
  } else {
    // User ID matches, but we might not have full data - try to load persisted data
    await userStore.loadPersistedUser()
  }
})
</script>

<template>
  <AppLayout v-if="layout === 'app'"/>
  <EmptyLayout v-else-if="layout === 'empty'"/>
  <AppLayout v-else/>
</template>