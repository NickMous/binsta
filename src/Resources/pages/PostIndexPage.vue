<script setup lang="ts">
import {onBeforeUnmount, onMounted, ref} from 'vue'
import {RouterLink} from 'vue-router'
import {Post, type PostApiResponse} from '@/entities/Post'
import {useBreadcrumbStore} from '@/stores/BreadcrumbStore'
import {useUserStore} from '@/stores/UserStore'
import {Button} from '@/components/ui/button'
import {Card, CardContent, CardFooter, CardHeader} from '@/components/ui/card'
import {Badge} from '@/components/ui/badge'
import {Skeleton} from '@/components/ui/skeleton'

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

    <!-- Error State -->
    <div v-if="error && !isLoading" class="flex flex-col items-center justify-center py-12">
      <div class="text-center">
        <div class="text-6xl mb-4">😕</div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Oops!</h2>
        <p class="text-gray-600 mb-4">{{ error }}</p>
        <Button variant="outline" @click="fetchPosts">
          Try Again
        </Button>
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
      <Card v-for="post in posts" :key="post.id" class="h-fit hover:shadow-lg transition-shadow cursor-pointer" @click="$router.push(`/posts/${post.id}`)">
        <CardHeader>
          <div class="flex items-start justify-between">
            <div class="space-y-1 flex-1">
              <h3 class="font-semibold text-lg leading-tight line-clamp-2">
                {{ post.getDisplayTitle() }}
              </h3>
              <div class="flex items-center gap-2">
                <Badge variant="secondary" class="text-xs">
                  {{ post.getLanguageDisplayName() }}
                </Badge>
                <span class="text-xs text-muted-foreground">
                  {{ post.getRelativeTime() }}
                </span>
              </div>
            </div>
          </div>
        </CardHeader>
        
        <CardContent class="space-y-4">
          <!-- Description -->
          <p class="text-sm text-muted-foreground line-clamp-3">
            {{ post.description }}
          </p>
          
          <!-- Code Preview -->
          <div class="bg-gray-950 rounded-md p-3 overflow-auto max-h-24">
            <pre class="text-xs text-gray-300 font-mono whitespace-pre-wrap"><code>{{ post.getCodePreview() }}</code></pre>
          </div>
        </CardContent>
        
        <CardFooter class="flex items-center justify-between pt-4">
          <div class="flex items-center gap-2 text-xs text-muted-foreground">
            <span>{{ post.code.length }} characters</span>
          </div>
          <Button variant="ghost" size="sm" @click.stop="$router.push(`/posts/${post.id}`)" class="h-8 px-3">
            View →
          </Button>
        </CardFooter>
      </Card>
    </div>

    <!-- Empty State -->
    <div v-else class="flex flex-col items-center justify-center py-16">
      <div class="text-center space-y-4">
        <div class="text-6xl mb-4">📝</div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">No posts yet</h2>
        <p class="text-muted-foreground max-w-md">
          Be the first to share a code snippet! Create your first post to get the community started.
        </p>
        <div class="flex gap-3 justify-center mt-6">
          <Button v-if="userStore.getIsAuthenticated" as-child>
            <RouterLink to="/posts/create">
              Create First Post
            </RouterLink>
          </Button>
          <Button v-else as-child>
            <RouterLink to="/login">
              Sign In to Post
            </RouterLink>
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-4 {
  display: -webkit-box;
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>