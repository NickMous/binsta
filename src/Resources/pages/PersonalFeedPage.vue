<script setup lang="ts">
import {onBeforeUnmount, onMounted, ref} from 'vue'
import {RouterLink} from 'vue-router'
import {Post, type PostApiResponse} from '@/entities/Post'
import {useBreadcrumbStore} from '@/stores/BreadcrumbStore'
import {useUserStore} from '@/stores/UserStore'
import {Button} from '@/components/ui/button'
import PostGrid from '@/components/PostGrid.vue'

const breadcrumbStore = useBreadcrumbStore()
const userStore = useUserStore()

const posts = ref<Post[]>([])
const isLoading = ref(true)
const error = ref<string | null>(null)

// Add abort controller for cleanup
let abortController: AbortController | null = null

async function fetchPersonalFeed() {
  try {
    // Cancel previous request if still pending
    if (abortController) {
      abortController.abort()
    }
    
    // Create new abort controller for this request
    abortController = new AbortController()
    
    isLoading.value = true
    error.value = null
    
    const response = await fetch('/api/posts/feed', {
      signal: abortController.signal
    })
    
    if (!response.ok) {
      if (response.status === 401) {
        error.value = "You need to be logged in to view your personal feed"
      } else {
        error.value = "Failed to load your personal feed"
      }
      return
    }
    
    const data = await response.json()
    
    // Map the posts array from the API response
    if (data.posts && Array.isArray(data.posts)) {
      posts.value = data.posts.map((postData: PostApiResponse) => Post.fromApiResponse(postData))
    } else {
      posts.value = []
    }
  } catch (err) {
    // Don't show error if request was aborted (user navigated away)
    if (err instanceof Error && err.name === 'AbortError') {
      return
    }
    console.error("Error fetching personal feed:", err)
    error.value = "An unexpected error occurred while loading your personal feed"
  } finally {
    isLoading.value = false
  }
}

// Set up breadcrumbs
onMounted(() => {
  breadcrumbStore.replaceBreadcrumbs([
    {
      name: 'Home',
      path: '/',
    },
    {
      name: 'Personal Feed',
      path: '/feed',
    }
  ])
  
  fetchPersonalFeed()
})

// Cleanup on component unmount
onBeforeUnmount(() => {
  if (abortController) {
    abortController.abort()
  }
  breadcrumbStore.clearBreadcrumbs()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
          Your Personal Feed
        </h1>
        <p class="text-muted-foreground mt-2">
          Posts from people you follow
        </p>
      </div>
      
      <div class="flex gap-3">
        <Button as-child variant="outline">
          <RouterLink to="/">
            Explore All Posts
          </RouterLink>
        </Button>
        <Button v-if="userStore.getIsAuthenticated" as-child>
          <RouterLink to="/posts/create">
            Create Post
          </RouterLink>
        </Button>
      </div>
    </div>

    <PostGrid
      :posts="posts"
      :is-loading="isLoading"
      :error="error"
      :empty-state-config="{
        icon: '👥',
        title: 'Your feed is empty',
        description: 'Follow some users to see their posts in your personal feed. Or explore all posts to discover interesting content!',
        actionText: 'Explore All Posts',
        actionTo: '/'
      }"
      :on-retry="fetchPersonalFeed"
    >
      <template #error-actions>
        <Button v-if="!userStore.getIsAuthenticated" as-child>
          <RouterLink to="/login">
            Sign In
          </RouterLink>
        </Button>
      </template>
      <template #empty-actions>
        <Button v-if="userStore.getIsAuthenticated" as-child variant="outline">
          <RouterLink to="/posts/create">
            Create Post
          </RouterLink>
        </Button>
      </template>
    </PostGrid>
  </div>
</template>