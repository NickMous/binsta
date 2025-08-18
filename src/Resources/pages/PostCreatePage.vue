<script setup lang="ts">
import {computed, reactive, ref} from 'vue'
import {useRouter} from 'vue-router'
import {useUserStore} from '@/stores/UserStore'
import {type IBreadcrumb, useBreadcrumbStore} from "@/stores/BreadcrumbStore.ts"
import {Separator} from '@/components/ui/separator'
import {Input} from "@/components/ui/input"
import {Label} from "@/components/ui/label"
import {Textarea} from "@/components/ui/textarea"
import {Button} from "@/components/ui/button"
import {
  Combobox,
  ComboboxAnchor,
  ComboboxEmpty,
  ComboboxGroup,
  ComboboxInput,
  ComboboxItem,
  ComboboxList
} from "@/components/ui/combobox"
import CodeHighlighter from '@/components/CodeHighlighter.vue'
import { CODE_THEMES, type CodeTheme } from '@/constants/codeThemes'

const router = useRouter()
const breadcrumbStore = useBreadcrumbStore()
const userStore = useUserStore()

const breadcrumbs: IBreadcrumb[] = [
  {name: 'Posts', path: '/posts'},
  {name: 'Create Post', path: '/posts/create'}
]

breadcrumbStore.replaceBreadcrumbs(breadcrumbs)

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

// Form state
const isCreating = ref(false)

interface ProgrammingLanguage {
  value: string
  label: string
}

interface CodeThemeOption {
  value: CodeTheme
  label: string
  description: string
}

const postForm = reactive<{
  title: string
  description: string
  code: string
  programming_language: string | ProgrammingLanguage
  code_theme: string | CodeThemeOption
}>({
  title: '',
  description: '',
  code: '',
  programming_language: '',
  code_theme: 'github-dark'
})

const errors = ref<Record<string, string[]>>({})
const successMessage = ref<string>('')

// Filter languages and themes for combobox
const languageSearchQuery = ref('')
const themeSearchQuery = ref('')

const filteredLanguages = computed(() => {
  if (!languageSearchQuery.value) return programmingLanguages

  return programmingLanguages.filter(lang =>
      lang.label.toLowerCase().includes(languageSearchQuery.value.toLowerCase()) ||
      lang.value.toLowerCase().includes(languageSearchQuery.value.toLowerCase())
  )
})

const filteredThemes = computed(() => {
  if (!themeSearchQuery.value) return CODE_THEMES

  return CODE_THEMES.filter(theme =>
      theme.label.toLowerCase().includes(themeSearchQuery.value.toLowerCase()) ||
      theme.value.toLowerCase().includes(themeSearchQuery.value.toLowerCase()) ||
      theme.description.toLowerCase().includes(themeSearchQuery.value.toLowerCase())
  )
})

const selectedLanguage = computed(() => {
  // Handle both object and string values for programming_language
  if (typeof postForm.programming_language === 'object' && postForm.programming_language?.value) {
    return postForm.programming_language
  }
  return programmingLanguages.find(lang => lang.value === postForm.programming_language)
})

const selectedTheme = computed(() => {
  // Handle both object and string values for code_theme
  if (typeof postForm.code_theme === 'object' && postForm.code_theme?.value) {
    return postForm.code_theme
  }
  return CODE_THEMES.find(theme => theme.value === postForm.code_theme)
})

const createPost = async () => {
  if (isCreating.value) return

  isCreating.value = true
  errors.value = {}
  successMessage.value = ''

  try {
    // Prepare form data, extracting language value if it's an object
    const languageValue = typeof postForm.programming_language === 'object' 
      ? postForm.programming_language.value 
      : postForm.programming_language

    const themeValue = typeof postForm.code_theme === 'object'
      ? postForm.code_theme.value
      : postForm.code_theme

    const response = await fetch('/api/posts', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        title: postForm.title,
        description: postForm.description,
        code: postForm.code,
        programming_language: languageValue,
        code_theme: themeValue,
        user_id: userStore.getId
      })
    })

    const data = await response.json()

    if (!response.ok) {
      if (data.fields) {
        errors.value = data.fields
      } else {
        throw new Error(data.message || 'Failed to create post')
      }
      return
    }

    // Show success message
    successMessage.value = 'Post created successfully!'

    // Redirect to the created post after a short delay
    setTimeout(() => {
      router.push(`/posts/${data.post.id}`)
    }, 1500)

  } catch (error) {
    console.error('Post creation error:', error)
    errors.value = {general: ['Failed to create post. Please try again.']}
  } finally {
    isCreating.value = false
  }
}


const clearForm = () => {
  postForm.title = ''
  postForm.description = ''
  postForm.code = ''
  postForm.programming_language = ''
  postForm.code_theme = 'github-dark'
  errors.value = {}
  successMessage.value = ''
}
</script>

<template>
  <div class="grid auto-rows-min gap-4 md:grid-cols-3">
    <div class="space-y-6 col-span-2">
      <div>
        <h3 class="text-lg font-medium">Create New Post</h3>
        <p class="text-sm text-muted-foreground">
          Share your code snippet with the community.
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
        <p
v-if="errors.description" id="description-error" class="text-red-500 text-sm" role="alert"
           aria-live="polite">
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

      <!-- Code Theme Field -->
      <div class="grid gap-2">
        <Label for="theme">Code Theme *</Label>
        <Combobox v-model="postForm.code_theme" by="value">
          <ComboboxAnchor>
            <ComboboxInput
              v-model="themeSearchQuery"
              :display-value="(val) => val?.label ?? ''"
              :placeholder="selectedTheme?.label || 'Select a code theme...'"
              class="w-full"
              :class="{ 'border-red-500': errors.code_theme }"
            />
          </ComboboxAnchor>
          <ComboboxList>
            <ComboboxEmpty>No themes found.</ComboboxEmpty>
            <ComboboxGroup>
              <ComboboxItem
                v-for="theme in filteredThemes"
                :key="theme.value"
                :value="theme"
              >
                <div class="flex flex-col">
                  <span class="font-medium">{{ theme.label }}</span>
                  <span class="text-xs text-muted-foreground">{{ theme.description }}</span>
                </div>
              </ComboboxItem>
            </ComboboxGroup>
          </ComboboxList>
        </Combobox>
        <p class="text-sm text-muted-foreground">
          Choose the color theme for code syntax highlighting.
        </p>
        <p v-if="errors.code_theme" class="text-red-500 text-sm" role="alert" aria-live="polite">
          {{ errors.code_theme }}
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
          Paste the code you want to share. It will be highlighted based on the selected language.
        </p>
        <p v-if="errors.code" id="code-error" class="text-red-500 text-sm" role="alert" aria-live="polite">
          {{ errors.code }}
        </p>
      </div>

      <!-- Code Preview -->
      <div v-if="postForm.code && selectedLanguage && selectedTheme" class="grid gap-2">
        <div class="flex items-center justify-between">
          <Label class="text-base font-medium">Code Preview</Label>
          <div class="flex items-center gap-2 text-sm text-muted-foreground">
            <span class="px-2 py-1 bg-muted rounded text-xs font-mono">
              {{ selectedLanguage.label }}
            </span>
            <span class="px-2 py-1 bg-muted rounded text-xs font-mono">
              {{ selectedTheme.label }}
            </span>
          </div>
        </div>
        <div class="border rounded-lg overflow-hidden bg-gray-950">
          <CodeHighlighter
              :code="postForm.code"
              :language="selectedLanguage.value"
              :theme="(selectedTheme?.value || 'github-dark') as CodeTheme"
          />
        </div>
        <p class="text-xs text-muted-foreground">
          This is how your code will appear to other users on the platform.
        </p>
      </div>

      <!-- Form Actions -->
      <div class="flex justify-between items-center">
        <div class="flex gap-2">
          <Button
              variant="outline"
              :disabled="isCreating"
              @click="clearForm"
          >
            Clear Form
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
              :disabled="isCreating || !postForm.title || !postForm.description || !postForm.code || !selectedLanguage || !selectedTheme"
              @click="createPost"
          >
            <span v-if="isCreating">Creating Post...</span>
            <span v-else>Create Post</span>
          </Button>
        </div>
      </div>
    </div>

    <!-- Right Column - Tips and Language Status -->
    <div class="space-y-4 col-span-2 md:col-span-1">
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

      <!-- Tips -->
      <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Tips for Great Posts</h4>
        <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
          <li>• Use clear, descriptive titles</li>
          <li>• Explain what your code does</li>
          <li>• Include context and use cases</li>
          <li>• Choose the correct language for highlighting</li>
          <li>• Keep code examples focused and concise</li>
        </ul>
      </div>
    </div>
  </div>
</template>