<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { createHighlighter, type Highlighter } from 'shiki'

import type { CodeTheme } from '@/constants/codeThemes'

interface Props {
  code: string
  language: string
  theme?: CodeTheme
}

const props = withDefaults(defineProps<Props>(), {
  theme: 'github-dark'
})

const highlightedHtml = ref('')
const isLoading = ref(true)
const error = ref('')

let highlighter: Highlighter | null = null

onMounted(async () => {
  try {
    highlighter = await createHighlighter({
      themes: ['github-dark', 'github-light', 'nord', 'one-dark-pro', 'dracula', 'monokai', 'tokyo-night', 'catppuccin-frappe'],
      langs: [
        'javascript', 'typescript', 'php', 'python', 'java', 'html', 'css', 'json',
        'bash', 'shell', 'c', 'cpp', 'csharp', 'go', 'rust', 'ruby', 'kotlin',
        'swift', 'sql', 'yaml', 'xml', 'vue', 'jsx', 'tsx'
      ]
    })
    
    updateHighlight()
  } catch (err) {
    console.error('Failed to initialize highlighter:', err)
    error.value = 'Failed to load syntax highlighter'
  } finally {
    isLoading.value = false
  }
})

const updateHighlight = () => {
  if (highlighter && props.code && props.language) {
    try {
      highlightedHtml.value = highlighter.codeToHtml(props.code, {
        lang: props.language.toLowerCase(),
        theme: props.theme
      })
    } catch (err) {
      console.error('Failed to highlight code:', err)
      // Fallback to plain text
      highlightedHtml.value = `<pre><code>${escapeHtml(props.code)}</code></pre>`
    }
  }
}

const escapeHtml = (text: string): string => {
  const div = document.createElement('div')
  div.textContent = text
  return div.innerHTML
}

watch(() => [props.code, props.language, props.theme], updateHighlight)
</script>

<template>
  <div class="code-highlighter">
    <!-- Loading State -->
    <div v-if="isLoading" class="animate-pulse bg-gray-100 dark:bg-gray-800 rounded-lg p-4">
      <div class="space-y-2">
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-5/6"></div>
      </div>
    </div>
    
    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
      <p class="text-red-600 dark:text-red-400 text-sm">{{ error }}</p>
      <pre class="mt-2 text-sm text-gray-600 dark:text-gray-400"><code>{{ code }}</code></pre>
    </div>
    
    <!-- Highlighted Code -->
    <div v-else class="code-container w-full min-w-0 overflow-x-auto">
      <div class="min-w-0" v-html="highlightedHtml"></div>
    </div>
  </div>
</template>

<style scoped>
@reference '@/style.css';

.code-container {
  @apply w-full min-w-0;
}

.code-container :deep(pre) {
  @apply rounded-lg overflow-x-auto w-full min-w-0;
  margin: 0;
  max-width: 100%;
}

.code-container :deep(code) {
  @apply block p-4;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  font-size: 14px;
  line-height: 1.5;
  max-width: 100%;
  white-space: pre;
}

/* Ensure proper scrolling on mobile */
.code-container :deep(*) {
  max-width: 100%;
}
</style>