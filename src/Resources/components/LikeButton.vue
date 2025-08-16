<script setup lang="ts">
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { LikeService } from '@/services/LikeService'
import { useUserStore } from '@/stores/UserStore'

interface Props {
  postId: number
  liked: boolean
  likeCount: number
  size?: 'sm' | 'default' | 'lg'
  variant?: 'default' | 'outline' | 'ghost'
}

const props = withDefaults(defineProps<Props>(), {
  size: 'sm',
  variant: 'ghost'
})

const emit = defineEmits<{
  updated: [liked: boolean, likeCount: number]
}>()

const userStore = useUserStore()
const isLoading = ref(false)
const localLiked = ref(props.liked)
const localLikeCount = ref(props.likeCount)

// Watch for prop changes to keep local state in sync
watchEffect(() => {
  localLiked.value = props.liked
  localLikeCount.value = props.likeCount
})

async function handleLikeToggle() {
  if (!userStore.getIsAuthenticated) {
    // Redirect to login or show auth modal
    return
  }

  if (isLoading.value) return

  const previousLiked = localLiked.value
  const previousCount = localLikeCount.value

  try {
    isLoading.value = true
    
    // Optimistic update
    localLiked.value = !localLiked.value
    localLikeCount.value += localLiked.value ? 1 : -1

    // Make API call
    const response = await LikeService.toggleLike(props.postId, previousLiked)
    
    // Update with server response
    localLiked.value = response.liked
    localLikeCount.value = response.like_count
    
    // Emit the updated values
    emit('updated', response.liked, response.like_count)
  } catch (error) {
    // Revert optimistic update on error
    localLiked.value = previousLiked
    localLikeCount.value = previousCount
    
    console.error('Failed to toggle like:', error)
  } finally {
    isLoading.value = false
  }
}

function formatLikeCount(count: number): string {
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
    class="gap-1"
    @click.stop="handleLikeToggle"
  >
    <span 
      :class="[
        'transition-colors',
        localLiked ? 'text-red-500' : 'text-muted-foreground'
      ]"
    >
      {{ localLiked ? '❤️' : '🤍' }}
    </span>
    <span class="text-xs">{{ formatLikeCount(localLikeCount) }}</span>
  </Button>
</template>

<script lang="ts">
import { watchEffect } from 'vue'
export default {
  name: 'LikeButton'
}
</script>