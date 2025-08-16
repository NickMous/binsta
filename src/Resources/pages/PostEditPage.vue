<script setup lang="ts">
import {computed, onBeforeUnmount, reactive, ref, watch} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import {Post, type PostApiResponse} from '@/entities/Post'
import {useUserStore} from '@/stores/UserStore'
import {useBreadcrumbStore} from '@/stores/BreadcrumbStore.ts'
import {Separator} from '@/components/ui/separator'
import {Input} from '@/components/ui/input'
import {Label} from '@/components/ui/label'
import {Textarea} from '@/components/ui/textarea'
import {Button} from '@/components/ui/button'
import {
  Combobox,
  ComboboxAnchor,
  ComboboxEmpty,
  ComboboxGroup,
  ComboboxInput,
  ComboboxItem,
  ComboboxList
} from '@/components/ui/combobox'
import CodeHighlighter from '@/components/CodeHighlighter.vue'

const route = useRoute()
const router = useRouter()
const breadcrumbStore = useBreadcrumbStore()
const userStore = useUserStore()

// Make post ID reactive to route changes
const postId = computed(() => route.params.id as string)

// Programming languages for the combobox
const programmingLanguages = [
  {value: 'javascript', label: 'JavaScript'},
  {value: 'typescript', label: 'TypeScript'},
  {value: 'php', label: 'PHP'},
  {value: 'python', label: 'Python'},
  {value: 'java', label: 'Java'},
  {value: 'html', label: 'HTML'},
  {value: 'css', label: 'CSS'},
  {value: 'json', label: 'JSON'},
  {value: 'bash', label: 'Bash'},
  {value: 'shell', label: 'Shell'},
  {value: 'c', label: 'C'},
  {value: 'cpp', label: 'C++'},
  {value: 'csharp', label: 'C#'},
  {value: 'go', label: 'Go'},
  {value: 'rust', label: 'Rust'},
  {value: 'ruby', label: 'Ruby'},
  {value: 'kotlin', label: 'Kotlin'},
  {value: 'swift', label: 'Swift'},
  {value: 'sql', label: 'SQL'},
  {value: 'yaml', label: 'YAML'},
  {value: 'xml', label: 'XML'},
  {value: 'vue', label: 'Vue'},
  {value: 'jsx', label: 'JSX'},
  {value: 'tsx', label: 'TSX'}
]

// State
const post = ref<Post | null>(null)
const isLoading = ref(true)
const isUpdating = ref(false)
const error = ref<string | null>(null)

// Add abort controller for cleanup
let abortController: AbortController | null = null

interface ProgrammingLanguage {
  value: string
  label: string
}

const postForm = reactive<{
  title: string
  description: string
  code: string
  programming_language: string | ProgrammingLanguage
}>({
  title: '',
  description: '',
  code: '',
  programming_language: ''
})

const errors = ref<Record<string, string>>({})
const successMessage = ref<string>('')

// Filter languages for combobox
const languageSearchQuery = ref('')
const filteredLanguages = computed(() => {
  if (!languageSearchQuery.value) return programmingLanguages

  return programmingLanguages.filter(lang =>
      lang.label.toLowerCase().includes(languageSearchQuery.value.toLowerCase()) ||
      lang.value.toLowerCase().includes(languageSearchQuery.value.toLowerCase())
  )
})

const selectedLanguage = computed(() => {
  // Handle both object and string values for programming_language
  if (typeof postForm.programming_language === 'object' && postForm.programming_language?.value) {
    return postForm.programming_language
  }
  return programmingLanguages.find(lang => lang.value === postForm.programming_language)
})

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
    
    // Check if user can edit this post
    if (post.value.userId !== userStore.getId) {
      error.value = "You don't have permission to edit this post"
      return
    }
    
    // Populate form with post data
    postForm.title = post.value.title
    postForm.description = post.value.description
    postForm.code = post.value.code
    postForm.programming_language = post.value.programmingLanguage
    
    // Update breadcrumbs
    breadcrumbStore.replaceBreadcrumbs([
      {
        name: 'Posts',
        path: '/posts',
      },
      {
        name: post.value.getDisplayTitle(),
        path: `/posts/${post.value.id}`,
      },
      {
        name: 'Edit',
        path: `/posts/${post.value.id}/edit`,
      }
    ])
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

const updatePost = async () => {
  if (isUpdating.value || !post.value) return

  isUpdating.value = true
  errors.value = {}
  successMessage.value = ''

  try {
    // Prepare form data, extracting language value if it's an object
    const languageValue = typeof postForm.programming_language === 'object' 
      ? postForm.programming_language.value 
      : postForm.programming_language

    const response = await fetch(`/api/posts/${post.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        title: postForm.title,
        description: postForm.description,
        code: postForm.code,
        programming_language: languageValue
      })
    })

    const data = await response.json()

    if (!response.ok) {
      if (data.fields) {
        errors.value = data.fields
      } else {
        throw new Error(data.message || 'Failed to update post')
      }
      return
    }

    // Show success message
    successMessage.value = 'Post updated successfully!'

    // Update the local post data
    post.value = Post.fromApiResponse(data.post as PostApiResponse)

    // Redirect to the post after a short delay
    setTimeout(() => {
      router.push(`/posts/${post.value!.id}`)
    }, 1500)

  } catch (error) {
    console.error('Post update error:', error)
    errors.value = {general: 'Failed to update post. Please try again.'}
  } finally {
    isUpdating.value = false
  }
}

const cancelEdit = () => {
  router.push(`/posts/${post.value!.id}`)
}
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
          <RouterLink to="/">
            Go to Home
          </RouterLink>
        </Button>
      </div>
    </div>
  </div>

  <!-- Loading State -->
  <div v-else-if="isLoading" class="space-y-6">
    <div class="space-y-2">
      <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-1/3"></div>
      <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-2/3"></div>
    </div>
    <div class="h-px bg-gray-200 dark:bg-gray-700"></div>
    <div class="space-y-4">
      <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded"></div>
      <div class="h-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
      <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded"></div>
      <div class="h-64 bg-gray-200 dark:bg-gray-700 rounded"></div>
    </div>
  </div>

  <!-- Edit Form -->
  <div v-else-if="post" class="grid auto-rows-min gap-4 md:grid-cols-3">
    <div class="space-y-6 col-span-2">
      <div>
        <h3 class="text-lg font-medium">Edit Post</h3>
        <p class="text-sm text-muted-foreground">
          Update your code snippet details.
        </p>
      </div>

      <Separator/>

      <!-- Title Field -->
      <div class="grid gap-2">
        <Label for="title">Title *</Label>
        <Input
            id="title"
            v-model="postForm.title"
            type="text"
            placeholder="Enter a descriptive title for your code snippet"
            required
            :class="{ 'border-red-500': errors.title }"
            :aria-describedby="errors.title ? 'title-error' : undefined"
            :aria-invalid="!!errors.title"
        />
        <p class="text-sm text-muted-foreground">
          Give your code snippet a clear, descriptive title.
        </p>
        <p v-if="errors.title" id="title-error" class="text-red-500 text-sm" role="alert" aria-live="polite">
          {{ errors.title }}
        </p>
      </div>

      <!-- Description Field -->
      <div class="grid gap-2">
        <Label for="description">Description *</Label>
        <Textarea
            id="description"
            v-model="postForm.description"
            placeholder="Describe what your code does, how it works, or when to use it..."
            rows="3"
            required
            :class="{ 'border-red-500': errors.description }"
            :aria-describedby="errors.description ? 'description-error' : undefined"
            :aria-invalid="!!errors.description"
        />
        <p class="text-sm text-muted-foreground">
          Explain what your code does and provide context for other developers.
        </p>
        <p v-if="errors.description" id="description-error" class="text-red-500 text-sm" role="alert" aria-live="polite">
          {{ errors.description }}
        </p>
      </div>

      <!-- Programming Language Field -->
      <div class="grid gap-2">
        <Label for="language">Programming Language *</Label>
        <Combobox v-model="postForm.programming_language" by="value">
          <ComboboxAnchor>
            <ComboboxInput
              v-model="languageSearchQuery"
              :display-value="(val) => val?.label ?? ''"
              :placeholder="selectedLanguage?.label || 'Select a programming language...'"
              class="w-full"
              :class="{ 'border-red-500': errors.programming_language }"
            />
          </ComboboxAnchor>
          <ComboboxList>
            <ComboboxEmpty>No languages found.</ComboboxEmpty>
            <ComboboxGroup>
              <ComboboxItem
                v-for="language in filteredLanguages"
                :key="language.value"
                :value="language"
              >
                {{ language.label }}
              </ComboboxItem>
            </ComboboxGroup>
          </ComboboxList>
        </Combobox>
        <p class="text-sm text-muted-foreground">
          Choose the programming language for syntax highlighting.
        </p>
        <p v-if="errors.programming_language" class="text-red-500 text-sm" role="alert" aria-live="polite">
          {{ errors.programming_language }}
        </p>
      </div>

      <!-- Code Field -->
      <div class="grid gap-2">
        <Label for="code">Code *</Label>
        <Textarea
            id="code"
            v-model="postForm.code"
            placeholder="Paste your code here..."
            rows="12"
            required
            class="font-mono text-sm"
            :class="{ 'border-red-500': errors.code }"
            :aria-describedby="errors.code ? 'code-error' : undefined"
            :aria-invalid="!!errors.code"
        />
        <p class="text-sm text-muted-foreground">
          Update the code you want to share. It will be highlighted based on the selected language.
        </p>
        <p v-if="errors.code" id="code-error" class="text-red-500 text-sm" role="alert" aria-live="polite">
          {{ errors.code }}
        </p>
      </div>

      <!-- Code Preview -->
      <div v-if="postForm.code && selectedLanguage" class="grid gap-2">
        <div class="flex items-center justify-between">
          <Label class="text-base font-medium">Code Preview</Label>
          <div class="flex items-center gap-2 text-sm text-muted-foreground">
            <span class="px-2 py-1 bg-muted rounded text-xs font-mono">
              {{ selectedLanguage.label }}
            </span>
          </div>
        </div>
        <div class="border rounded-lg overflow-hidden bg-gray-950">
          <CodeHighlighter
              :code="postForm.code"
              :language="selectedLanguage.value"
              theme="github-dark"
          />
        </div>
        <p class="text-xs text-muted-foreground">
          This is how your updated code will appear to other users.
        </p>
      </div>

      <!-- Form Actions -->
      <div class="flex justify-between items-center">
        <div class="flex gap-2">
          <Button
              variant="outline"
              :disabled="isUpdating"
              @click="cancelEdit"
          >
            Cancel
          </Button>
        </div>

        <div class="flex items-center gap-4">
          <div class="flex flex-col gap-1 text-right">
            <p v-if="successMessage" class="text-green-600 text-sm" role="alert" aria-live="polite">
              {{ successMessage }}
            </p>
            <p v-if="errors.general" class="text-red-500 text-sm" role="alert" aria-live="polite">
              {{ errors.general }}
            </p>
          </div>
          <Button
              :disabled="isUpdating || !postForm.title || !postForm.description || !postForm.code || !selectedLanguage"
              @click="updatePost"
          >
            <span v-if="isUpdating">Updating Post...</span>
            <span v-else>Update Post</span>
          </Button>
        </div>
      </div>
    </div>

    <!-- Right Column - Info -->
    <div class="space-y-4">
      <!-- Language Selection Status -->
      <div v-if="selectedLanguage" class="bg-gray-50 dark:bg-gray-900 border rounded-lg p-4">
        <h4 class="font-medium mb-2">Selected Language</h4>
        <div class="flex items-center gap-2">
          <div class="w-3 h-3 bg-green-500 rounded-full"></div>
          <p class="text-sm text-muted-foreground">
            <strong>{{ selectedLanguage.label }}</strong>
          </p>
        </div>
        <p class="text-xs text-muted-foreground mt-1">
          Syntax highlighting ready
        </p>
      </div>

      <!-- Post Info -->
      <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Post Info</h4>
        <div class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
          <div>Created: {{ post.getRelativeTime() }}</div>
          <div>Last updated: {{ new Date(post.updatedAt).toLocaleDateString() }}</div>
        </div>
      </div>
    </div>
  </div>
</template>