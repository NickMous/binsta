<script setup lang="ts">
import {RouterLink} from 'vue-router'
import type {Post} from '@/entities/Post'
import {Card, CardContent, CardFooter, CardHeader} from '@/components/ui/card'
import {Badge} from '@/components/ui/badge'
import {Button} from '@/components/ui/button'
import {Avatar, AvatarFallback, AvatarImage} from '@/components/ui/avatar'
import LikeButton from '@/components/LikeButton.vue'

interface Props {
  post: Post
  clickable?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  clickable: true
})

const emit = defineEmits<{
  cardClick: [post: Post]
  likeUpdated: [post: Post, liked: boolean, likeCount: number]
}>()

function handleCardClick() {
  if (props.clickable) {
    emit('cardClick', props.post)
  }
}

function handleLikeUpdated(liked: boolean, likeCount: number) {
  emit('likeUpdated', props.post, liked, likeCount)
}
</script>

<template>
  <Card 
    :class="[
      'h-fit transition-shadow',
      clickable ? 'hover:shadow-lg cursor-pointer' : ''
    ]" 
    @click="handleCardClick"
  >
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
          <!-- User information -->
          <div v-if="post.userName || post.userUsername" class="flex items-center gap-2 mt-2">
            <Avatar class="h-6 w-6">
              <AvatarImage v-if="post.userProfilePicture" :src="post.userProfilePicture" :alt="post.getUserDisplayName()" />
              <AvatarFallback class="text-xs">
                {{ post.getUserDisplayName().charAt(0).toUpperCase() }}
              </AvatarFallback>
            </Avatar>
            <div class="flex flex-col">
              <RouterLink 
                :to="`/users/${post.getUserUsername()}`"
                class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:underline"
                @click.stop
              >
                {{ post.getUserDisplayName() }}
              </RouterLink>
              <span v-if="post.getUserUsername()" class="text-xs text-muted-foreground">
                @{{ post.getUserUsername() }}
              </span>
            </div>
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
      <div class="flex items-center gap-3">
        <LikeButton
          :post-id="post.id"
          :liked="post.userLiked"
          :like-count="post.likeCount"
          @updated="handleLikeUpdated"
        />
        <div class="text-xs text-muted-foreground">
          {{ post.code.length }} chars
        </div>
      </div>
      <Button 
        variant="ghost" 
        size="sm" 
        class="h-8 px-3" 
        as-child
        @click.stop
      >
        <RouterLink :to="`/posts/${post.id}`">
          View →
        </RouterLink>
      </Button>
    </CardFooter>
  </Card>
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
</style>