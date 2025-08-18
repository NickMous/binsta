<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { ForkService } from '@/services/ForkService'
import { useUserStore } from '@/stores/UserStore'

interface Props {
  postId: number
  forked: boolean
  forkCount: number
  size?: 'sm' | 'default' | 'lg'
  variant?: 'default' | 'outline' | 'ghost'
  showCount?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  size: 'sm',
  variant: 'ghost',
  showCount: true
})

const emit = defineEmits<{
  updated: [forked: boolean, forkCount: number]
}>()

const userStore = useUserStore()
const router = useRouter()
const isLoading = ref(false)
const localForked = ref(props.forked)
const localForkCount = ref(props.forkCount)

// Watch for prop changes to keep local state in sync
watchEffect(() => {
  localForked.value = props.forked
  localForkCount.value = props.forkCount
})

async function handleFork() {
  if (!userStore.getIsAuthenticated) {
    // Redirect to login page
    router.push('/login')
    return
  }

  if (isLoading.value) return

  const previousForked = localForked.value
  const previousCount = localForkCount.value

  try {
    isLoading.value = true
    
    // Optimistic update
    localForked.value = true
    localForkCount.value += 1

    // Make API call
    const forkResponse = await ForkService.forkPost(props.postId)
    
    // Update with server response (get latest fork status)
    const status = await ForkService.getForkStatus(props.postId)
    localForked.value = status.user_forked
    localForkCount.value = status.fork_count
    
    // Emit the updated values
    emit('updated', status.user_forked, status.fork_count)
    
    // Redirect to edit the newly forked post
    if (forkResponse.forked_post) {
      const forkedPostId = forkResponse.forked_post.id
      router.push(`/posts/${forkedPostId}/edit`)
    }
  } catch (error) {
    // Revert optimistic update on error
    localForked.value = previousForked
    localForkCount.value = previousCount
    
    console.error('Failed to fork post:', error)
  } finally {
    isLoading.value = false
  }
}

function formatForkCount(count: number): string {
  if (count === 0) return '0'
  if (count === 1) return '1'
  if (count < 1000) return count.toString()
  if (count < 1000000) return `${(count / 1000).toFixed(1)}k`
  return `${(count / 1000000).toFixed(1)}m`
}
</script>

<template>
  <Button
    :variant="variant"
    :size="size"
    :disabled="isLoading"
    :title="'Fork this post to create and edit your own copy'"
    class="gap-1"
    @click.stop="handleFork"
  >
    <span 
      :class="[
        'transition-colors',
        localForked ? 'text-blue-500' : 'text-muted-foreground'
      ]"
    >
      🍴
    </span>
    <span v-if="showCount" class="text-xs">{{ formatForkCount(localForkCount) }}</span>
  </Button>
</template>

<script lang="ts">
import { watchEffect } from 'vue'
export default {
  name: 'ForkButton'
}
</script>