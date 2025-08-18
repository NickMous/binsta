<script setup lang="ts">
import {computed, ref, watch} from 'vue'
import {Comment, type CommentApiResponse} from '@/entities/Comment'
import {useUserStore} from '@/stores/UserStore'
import {Button} from '@/components/ui/button'
import {Textarea} from '@/components/ui/textarea'
import {Card, CardContent} from '@/components/ui/card'
import {Avatar, AvatarFallback, AvatarImage} from '@/components/ui/avatar'

interface Props {
  postId: number
}

const props = defineProps<Props>()
const userStore = useUserStore()

const comments = ref<Comment[]>([])
const newCommentContent = ref('')
const isLoading = ref(false)
const isSubmitting = ref(false)
const error = ref<string | null>(null)

const isLoggedIn = computed(() => userStore.getIsAuthenticated)
const canComment = computed(() => isLoggedIn.value && newCommentContent.value.trim().length > 0)

// Fetch comments when component mounts or postId changes
watch(() => props.postId, fetchComments, { immediate: true })

async function fetchComments() {
  if (!props.postId) return
  
  try {
    isLoading.value = true
    error.value = null
    
    const response = await fetch(`/api/posts/${props.postId}/comments`)
    
    if (!response.ok) {
      throw new Error('Failed to fetch comments')
    }
    
    const data = await response.json()
    comments.value = data.comments.map((commentData: CommentApiResponse) => 
      Comment.fromApiResponse(commentData)
    )
  } catch (err) {
    console.error('Error fetching comments:', err)
    error.value = 'Failed to load comments'
  } finally {
    isLoading.value = false
  }
}

async function submitComment() {
  if (!canComment.value) return
  
  try {
    isSubmitting.value = true
    error.value = null
    
    const response = await fetch(`/api/posts/${props.postId}/comments`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        content: newCommentContent.value.trim(),
        post_id: props.postId,
        user_id: userStore.getId
      })
    })
    
    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Failed to create comment')
    }
    
    // Clear the input and refresh comments
    newCommentContent.value = ''
    await fetchComments()
  } catch (err) {
    console.error('Error creating comment:', err)
    error.value = err instanceof Error ? err.message : 'Failed to create comment'
  } finally {
    isSubmitting.value = false
  }
}

async function deleteComment(commentId: number) {
  if (!confirm('Are you sure you want to delete this comment?')) return
  
  try {
    const response = await fetch(`/api/comments/${commentId}`, {
      method: 'DELETE'
    })
    
    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Failed to delete comment')
    }
    
    // Remove comment from local state
    comments.value = comments.value.filter(comment => comment.id !== commentId)
  } catch (err) {
    console.error('Error deleting comment:', err)
    error.value = err instanceof Error ? err.message : 'Failed to delete comment'
  }
}

function canDeleteComment(comment: Comment): boolean {
  return isLoggedIn.value && comment.userId === userStore.getId
}
</script>

<template>
  <div class="space-y-6">
    <!-- Comments Header -->
    <div class="flex items-center justify-between">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
        Comments
        <span v-if="!isLoading" class="text-sm font-normal text-muted-foreground ml-2">
          ({{ comments.length }})
        </span>
      </h3>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
      <p class="text-red-700 dark:text-red-400 text-sm">{{ error }}</p>
    </div>

    <!-- Comment Form -->
    <Card v-if="isLoggedIn" class="border-2 border-dashed border-gray-200 dark:border-gray-700">
      <CardContent class="p-4">
        <div class="space-y-4">
          <div class="flex items-start gap-3">
            <Avatar class="h-8 w-8 flex-shrink-0">
              <AvatarImage v-if="userStore.getProfilePicture" :src="userStore.getProfilePicture" :alt="userStore.getName" />
              <AvatarFallback class="text-sm">
                {{ userStore.getName?.charAt(0).toUpperCase() || 'U' }}
              </AvatarFallback>
            </Avatar>
            <div class="flex-1">
              <Textarea
                v-model="newCommentContent"
                placeholder="Write a comment..."
                :disabled="isSubmitting"
                class="min-h-[80px] resize-none"
                @keydown.ctrl.enter="submitComment"
                @keydown.meta.enter="submitComment"
              />
              <div class="flex items-center justify-between mt-2">
                <p class="text-xs text-muted-foreground">
                  Press Ctrl/Cmd + Enter to submit
                </p>
                <div class="flex gap-2">
                  <Button
                    variant="outline"
                    size="sm"
                    :disabled="!newCommentContent.trim() || isSubmitting"
                    @click="newCommentContent = ''"
                  >
                    Cancel
                  </Button>
                  <Button
                    size="sm"
                    :disabled="!canComment || isSubmitting"
                    @click="submitComment"
                  >
                    {{ isSubmitting ? 'Posting...' : 'Post Comment' }}
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Login Prompt -->
    <Card v-else class="border-2 border-dashed border-gray-200 dark:border-gray-700">
      <CardContent class="p-4 text-center">
        <p class="text-muted-foreground">
          <RouterLink to="/login" class="text-primary hover:underline">
            Sign in
          </RouterLink>
          to join the conversation
        </p>
      </CardContent>
    </Card>

    <!-- Comments List -->
    <div class="space-y-4">
      <!-- Loading State -->
      <div v-if="isLoading" class="space-y-4">
        <div v-for="i in 3" :key="i" class="flex gap-3 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
          <div class="h-8 w-8 bg-gray-200 dark:bg-gray-700 rounded-full animate-pulse"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse w-1/4"></div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse w-3/4"></div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse w-1/2"></div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="comments.length === 0" class="text-center py-8">
        <div class="text-4xl mb-2">💬</div>
        <p class="text-muted-foreground">No comments yet</p>
        <p class="text-sm text-muted-foreground mt-1">Be the first to share your thoughts!</p>
      </div>

      <!-- Comments -->
      <div v-else class="space-y-4">
        <Card v-for="comment in comments" :key="comment.id" class="border border-gray-200 dark:border-gray-700">
          <CardContent class="p-4">
            <div class="flex gap-3">
              <Avatar class="h-8 w-8 flex-shrink-0">
                <AvatarImage v-if="comment.userProfilePicture" :src="comment.userProfilePicture" :alt="comment.getUserDisplayName()" />
                <AvatarFallback class="text-sm">
                  {{ comment.getUserDisplayName().charAt(0).toUpperCase() }}
                </AvatarFallback>
              </Avatar>
              
              <div class="flex-1">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center gap-2">
                    <RouterLink 
                      v-if="comment.userUsername"
                      :to="`/users/${comment.userUsername}`"
                      class="font-medium text-gray-900 dark:text-gray-100 hover:underline"
                    >
                      {{ comment.getUserDisplayName() }}
                    </RouterLink>
                    <span v-else class="font-medium text-gray-900 dark:text-gray-100">
                      {{ comment.getUserDisplayName() }}
                    </span>
                    <span class="text-sm text-muted-foreground">
                      {{ comment.getRelativeTime() }}
                    </span>
                  </div>
                  
                  <Button
                    v-if="canDeleteComment(comment)"
                    variant="ghost"
                    size="sm"
                    class="text-muted-foreground hover:text-destructive"
                    @click="deleteComment(comment.id)"
                  >
                    Delete
                  </Button>
                </div>
                
                <div class="prose dark:prose-invert max-w-none">
                  <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                    {{ comment.content }}
                  </p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>