<template>
  <div class="space-y-6">
    <div class="text-center">
      <h1 class="text-3xl font-bold text-foreground">Trending</h1>
      <p class="text-muted-foreground mt-2">Discover what's popular right now</p>
    </div>
    
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      <div v-for="i in 6" :key="i" class="bg-card rounded-lg p-6 border border-border">
        <div class="flex items-center space-x-3 mb-4">
          <div class="h-10 w-10 rounded-full bg-gradient-to-br from-primary to-primary/60 flex items-center justify-center">
            <span class="text-sm font-bold text-primary-foreground">{{ i }}</span>
          </div>
          <div>
            <h3 class="font-semibold text-foreground">User {{ i }}</h3>
            <p class="text-sm text-muted-foreground">@user{{ i }}</p>
          </div>
        </div>
        <p class="text-foreground mb-4">This is a trending post #{{ i }}. It's getting lots of engagement!</p>
        <div class="flex items-center justify-between text-sm text-muted-foreground">
          <span>{{ 100 + i * 50 }} likes</span>
          <span>{{ 20 + i * 5 }} comments</span>
        </div>
      </div>
    </div>
  </div>
</template>