<template>
  <div class="space-y-6">
    <div class="text-center">
      <h1 class="text-3xl font-bold text-foreground">Following</h1>
      <p class="text-muted-foreground mt-2">Posts from people you follow</p>
    </div>
    
    <div class="max-w-2xl mx-auto space-y-4">
      <div v-for="i in 5" :key="i" class="bg-card rounded-lg p-6 border border-border">
        <div class="flex items-center space-x-3 mb-4">
          <div class="h-12 w-12 rounded-full bg-gradient-to-br from-primary to-primary/60 flex items-center justify-center">
            <span class="text-sm font-bold text-primary-foreground">F{{ i }}</span>
          </div>
          <div>
            <h3 class="font-semibold text-foreground">Friend {{ i }}</h3>
            <p class="text-sm text-muted-foreground">2 hours ago</p>
          </div>
        </div>
        <p class="text-foreground mb-4">Just shared an amazing moment! Following feed post #{{ i }}.</p>
        <div class="flex items-center space-x-4 text-sm text-muted-foreground">
          <button class="hover:text-foreground transition-colors">♥ {{ 50 + i * 10 }}</button>
          <button class="hover:text-foreground transition-colors">💬 {{ 5 + i * 2 }}</button>
          <button class="hover:text-foreground transition-colors">🔄 {{ 3 + i }}</button>
        </div>
      </div>
    </div>
  </div>
</template>