<template>
  <div class="max-w-2xl mx-auto space-y-6">
    <div class="text-center">
      <h1 class="text-3xl font-bold text-foreground">Create Post</h1>
      <p class="text-muted-foreground mt-2">Share something amazing with the world</p>
    </div>
    
    <div class="bg-card rounded-lg p-6 border border-border">
      <form @submit.prevent="createPost" class="space-y-4">
        <div>
          <label class="text-sm font-medium text-foreground mb-2 block">What's on your mind?</label>
          <textarea 
            v-model="postContent"
            class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
            placeholder="Write your post here..."
            rows="4"
          ></textarea>
        </div>
        
        <div class="flex items-center space-x-2">
          <button type="button" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
            📷 Photo
          </button>
          <button type="button" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
            🎥 Video
          </button>
          <button type="button" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
            😊 Emoji
          </button>
        </div>
        
        <div class="flex justify-end">
          <button 
            type="submit" 
            :disabled="!postContent.trim()"
            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2"
          >
            Share Post
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const postContent = ref('')

const createPost = () => {
  if (!postContent.value.trim()) return
  
  console.log('Creating post:', postContent.value)
  postContent.value = ''
}
</script>