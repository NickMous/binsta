<script setup lang="ts">
import {computed, onBeforeUnmount, ref, watch} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import {Post, type PostApiResponse} from '@/entities/Post'
import {useBreadcrumbStore} from '@/stores/BreadcrumbStore'
import {useUserStore} from '@/stores/UserStore'
import {Button} from '@/components/ui/button'
import {Skeleton} from '@/components/ui/skeleton'
import {Badge} from '@/components/ui/badge'
import {Avatar, AvatarFallback, AvatarImage} from '@/components/ui/avatar'
import CodeHighlighter from '@/components/CodeHighlighter.vue'

const route = useRoute()
const router = useRouter()
const breadcrumbStore = useBreadcrumbStore()
const userStore = useUserStore()

// Make post ID reactive to route changes
const postId = computed(() => route.params.id as string)

const post = ref<Post | null>(null)
const isLoading = ref(true)
const error = ref<string | null>(null)

// Add abort controller for cleanup
let abortController: AbortController | null = null

async function fetchPost() {
  try {
    // Cancel previous request if still pending
    if (abortController) {
      abortController.abort()
    }
    
    // Create new abort controller for this request
    abortController = new AbortController()
    
    isLoading.value = true
    error.value = null
    
    const response = await fetch(`/api/posts/${postId.value}`, {
      signal: abortController.signal
    })
    
    if (!response.ok) {
      if (response.status === 404) {
        error.value = `Post not found`
        return
      } else {
        error.value = "Failed to load post"
        return
      }
    }
    
    const data = await response.json()
    post.value = Post.fromApiResponse(data.post as PostApiResponse)
    
    // Update breadcrumbs with post title
    if (post.value) {
      breadcrumbStore.replaceBreadcrumbs([
        {
          name: 'Posts',
          path: '/posts',
        },
        {
          name: post.value.getDisplayTitle(),
          path: `/posts/${post.value.id}`,
        }
      ])
    }
  } catch (err) {
    // Don't show error if request was aborted (user navigated away)
    if (err instanceof Error && err.name === 'AbortError') {
      return
    }
    console.error("Error fetching post:", err)
    error.value = "An unexpected error occurred while loading the post"
  } finally {
    isLoading.value = false
  }
}

// Watch for postId changes and refetch data
watch(postId, () => {
  if (postId.value) {
    fetchPost()
  }
}, { immediate: true })

// Cleanup on component unmount
onBeforeUnmount(() => {
  if (abortController) {
    abortController.abort()
  }
  breadcrumbStore.clearBreadcrumbs()
})

async function deletePost() {
  if (!post.value || !confirm('Are you sure you want to delete this post?')) {
    return
  }
  
  try {
    const response = await fetch(`/api/posts/${post.value.id}`, {
      method: 'DELETE',
    })
    
    if (response.ok) {
      // Redirect to posts list after successful deletion
      router.push('/posts')
    } else {
      alert('Failed to delete post')
    }
  } catch (error) {
    console.error('Error deleting post:', error)
    alert('Failed to delete post')
  }
}

const canEditPost = computed(() => {
  return post.value && userStore.getId === post.value.userId
})
</script>

<template>
  <!-- Error State -->
  <div v-if="error && !isLoading" class="flex flex-col items-center justify-center py-12">
    <div class="text-center">
      <div class="text-6xl mb-4">😕</div>
      <h2 class="text-2xl font-bold text-gray-800 mb-2">Oops!</h2>
      <p class="text-gray-600 mb-4">{{ error }}</p>
      <div class="flex gap-3 justify-center">
        <Button variant="outline" @click="fetchPost">
          Try Again
        </Button>
        <Button as-child>
          <RouterLink to="/posts">
            Browse Posts
          </RouterLink>
        </Button>
      </div>
    </div>
  </div>

  <!-- Loading State -->
  <div v-else-if="isLoading" class="space-y-6">
    <div class="flex justify-between items-start">
      <div class="space-y-2">
        <Skeleton class="h-8 w-2/3" />
        <Skeleton class="h-4 w-1/3" />
      </div>
      <Skeleton class="h-10 w-24" />
    </div>
    <Skeleton class="h-20 w-full" />
    <Skeleton class="h-64 w-full" />
  </div>

  <!-- Post Content -->
  <div v-else-if="post" class="space-y-6">
    <!-- Post Header -->
    <div class="flex justify-between items-start">
      <div class="space-y-2">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
          {{ post.title }}
        </h1>
        <div class="flex items-center gap-4 text-sm text-muted-foreground">
          <span>{{ post.getRelativeTime() }}</span>
          <Badge variant="secondary">
            {{ post.getLanguageDisplayName() }}
          </Badge>
        </div>
        <!-- User information -->
        <div v-if="post.userName || post.userUsername" class="flex items-center gap-3">
          <Avatar class="h-8 w-8">
            <AvatarImage v-if="post.userProfilePicture" :src="post.userProfilePicture" :alt="post.getUserDisplayName()" />
            <AvatarFallback class="text-sm">
              {{ post.getUserDisplayName().charAt(0).toUpperCase() }}
            </AvatarFallback>
          </Avatar>
          <div class="flex flex-col">
            <RouterLink 
              :to="`/users/${post.getUserUsername()}`"
              class="text-base font-medium text-gray-900 dark:text-gray-100 hover:underline"
            >
              {{ post.getUserDisplayName() }}
            </RouterLink>
            <span v-if="post.getUserUsername()" class="text-sm text-muted-foreground">
              @{{ post.getUserUsername() }}
            </span>
          </div>
        </div>
      </div>
      
      <!-- Actions for post owner -->
      <div v-if="canEditPost" class="flex gap-2">
        <Button variant="outline" size="sm" as-child>
          <RouterLink :to="`/posts/${post.id}/edit`">
            Edit
          </RouterLink>
        </Button>
        <Button variant="destructive" size="sm" @click="deletePost">
          Delete
        </Button>
      </div>
    </div>

    <!-- Post Description -->
    <div class="prose dark:prose-invert max-w-none">
      <p class="text-lg text-gray-700 dark:text-gray-300">
        {{ post.description }}
      </p>
    </div>

    <!-- Code Section -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
          Code
        </h2>
        <div class="flex items-center gap-2">
          <Badge variant="outline">
            {{ post.getLanguageDisplayName() }}
          </Badge>
          <span class="text-sm text-muted-foreground">
            {{ post.getCodePreview().length }} characters
          </span>
        </div>
      </div>
      
      <div class="border rounded-lg overflow-hidden bg-gray-950">
        <CodeHighlighter
          :code="post.code"
          :language="post.programmingLanguage"
          theme="github-dark"
        />
      </div>
    </div>

    <!-- Post Footer -->
    <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <div class="text-sm text-muted-foreground">
          <span>Posted {{ post.getRelativeTime() }}</span>
          <span v-if="post.updatedAt !== post.createdAt">
            • Updated {{ new Date(post.updatedAt).toLocaleDateString() }}
          </span>
        </div>
        
        <div class="flex gap-2">
          <Button variant="outline" size="sm" as-child>
            <RouterLink to="/posts">
              ← Back to Posts
            </RouterLink>
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>