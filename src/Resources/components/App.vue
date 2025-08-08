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
onMounted(() => {
  userStore.loadPersistedUser()

  const user = parseInt(props.user);
  if (userStore.id !== user) {
    if (user === 0) {
      userStore.clearUser()
    } else {
      fetch(`/api/users/${props.user}`)
          .then(response => response.json())
          .then(data => {
            userStore.setUser(data)
          })
          .catch(error => {
            console.error('Failed to fetch user:', error)
            userStore.clearUser()
          })
    }
  }
})
</script>

<template>
  <AppLayout v-if="layout === 'app'"/>
  <EmptyLayout v-else-if="layout === 'empty'"/>
  <AppLayout v-else/>
</template>