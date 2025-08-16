<script setup lang="ts">
import {useRouter} from 'vue-router'
import type {Post} from '@/entities/Post'
import PostCard from '@/components/PostCard.vue'
import {Card, CardContent, CardFooter, CardHeader} from '@/components/ui/card'
import {Skeleton} from '@/components/ui/skeleton'
import {Button} from '@/components/ui/button'

interface Props {
  posts: Post[]
  isLoading?: boolean
  error?: string | null
  emptyStateConfig?: {
    icon: string
    title: string
    description: string
    actionText?: string
    actionTo?: string
  }
  onRetry?: () => void
}

withDefaults(defineProps<Props>(), {
  isLoading: false,
  error: null,
  emptyStateConfig: () => ({
    icon: '📝',
    title: 'No posts yet',
    description: 'Be the first to share a code snippet!',
    actionText: 'Create Post',
    actionTo: '/posts/create'
  })
})

const emit = defineEmits<{
  postLikeUpdated: [post: Post, liked: boolean, likeCount: number]
}>()

const router = useRouter()

function handleCardClick(post: Post) {
  router.push(`/posts/${post.id}`)
}

function handleLikeUpdated(post: Post, liked: boolean, likeCount: number) {
  emit('postLikeUpdated', post, liked, likeCount)
}
</script>

<template>
  <!-- Error State -->
  <div v-if="error && !isLoading" class="flex flex-col items-center justify-center py-12">
    <div class="text-center">
      <div class="text-6xl mb-4">😕</div>
      <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-2">Oops!</h2>
      <p class="text-gray-600 dark:text-gray-400 mb-4">{{ error }}</p>
      <div class="flex gap-3 justify-center">
        <Button v-if="onRetry" variant="outline" @click="onRetry">
          Try Again
        </Button>
        <slot name="error-actions"></slot>
      </div>
    </div>
  </div>

  <!-- Loading State -->
  <div v-else-if="isLoading" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
    <Card v-for="i in 6" :key="i" class="h-fit">
      <CardHeader>
        <Skeleton class="h-6 w-3/4" />
        <Skeleton class="h-4 w-1/2" />
      </CardHeader>
      <CardContent>
        <Skeleton class="h-16 w-full mb-4" />
        <Skeleton class="h-24 w-full" />
      </CardContent>
      <CardFooter>
        <Skeleton class="h-4 w-1/3" />
      </CardFooter>
    </Card>
  </div>

  <!-- Posts Grid -->
  <div v-else-if="posts.length > 0" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
    <PostCard 
      v-for="post in posts" 
      :key="post.id" 
      :post="post"
      @card-click="handleCardClick"
      @like-updated="handleLikeUpdated"
    />
  </div>

  <!-- Empty State -->
  <div v-else class="flex flex-col items-center justify-center py-16">
    <div class="text-center space-y-4">
      <div class="text-6xl mb-4">{{ emptyStateConfig.icon }}</div>
      <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ emptyStateConfig.title }}</h2>
      <p class="text-muted-foreground max-w-md">
        {{ emptyStateConfig.description }}
      </p>
      <div class="flex gap-3 justify-center mt-6">
        <Button v-if="emptyStateConfig.actionText && emptyStateConfig.actionTo" as-child>
          <RouterLink :to="emptyStateConfig.actionTo">
            {{ emptyStateConfig.actionText }}
          </RouterLink>
        </Button>
        <slot name="empty-actions"></slot>
      </div>
    </div>
  </div>
</template>