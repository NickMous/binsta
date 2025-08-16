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

async function fetchPosts() {
  try {
    // Cancel previous request if still pending
    if (abortController) {
      abortController.abort()
    }
    
    // Create new abort controller for this request
    abortController = new AbortController()
    
    isLoading.value = true
    error.value = null
    
    const response = await fetch('/api/posts', {
      signal: abortController.signal
    })
    
    if (!response.ok) {
      error.value = "Failed to load posts"
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
    console.error("Error fetching posts:", err)
    error.value = "An unexpected error occurred while loading posts"
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
    }
  ])
  
  fetchPosts()
})

// Handle like updates
function handlePostLikeUpdated(post: Post, liked: boolean, likeCount: number) {
  const postIndex = posts.value.findIndex(p => p.id === post.id)
  if (postIndex !== -1) {
    posts.value[postIndex] = post.updateLikeStatus(liked, likeCount)
  }
}

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
          Welcome to Binsta
        </h1>
        <p class="text-muted-foreground mt-2">
          Discover and share amazing code snippets with the community
        </p>
      </div>
      
      <Button v-if="userStore.getIsAuthenticated" as-child>
        <RouterLink to="/posts/create">
          Create Post
        </RouterLink>
      </Button>
      <Button v-else as-child variant="outline">
        <RouterLink to="/login">
          Sign In to Post
        </RouterLink>
      </Button>
    </div>

    <PostGrid
      :posts="posts"
      :is-loading="isLoading"
      :error="error"
      :empty-state-config="{
        icon: '📝',
        title: 'No posts yet',
        description: 'Be the first to share a code snippet! Create your first post to get the community started.',
        actionText: userStore.getIsAuthenticated ? 'Create First Post' : 'Sign In to Post',
        actionTo: userStore.getIsAuthenticated ? '/posts/create' : '/login'
      }"
      :on-retry="fetchPosts"
      @post-like-updated="handlePostLikeUpdated"
    />
  </div>
</template>