<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-card rounded-lg p-6 border border-border">
      <div class="flex items-start space-x-6">
        <div class="h-24 w-24 rounded-full bg-gradient-to-br from-primary to-primary/60 flex items-center justify-center">
          <span class="text-2xl font-bold text-primary-foreground">U</span>
        </div>
        <div class="flex-1">
          <h1 class="text-2xl font-bold text-foreground">Your Profile</h1>
          <p class="text-muted-foreground">@username</p>
          <p class="text-foreground mt-2">Welcome to your profile page! Here you can view and manage your posts.</p>
          <div class="flex items-center space-x-6 mt-4 text-sm text-muted-foreground">
            <span><strong class="text-foreground">42</strong> Posts</span>
            <span><strong class="text-foreground">1.2k</strong> Followers</span>
            <span><strong class="text-foreground">890</strong> Following</span>
          </div>
        </div>
        <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
          Edit Profile
        </button>
      </div>
    </div>
    
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      <div v-for="i in 9" :key="i" class="bg-card rounded-lg p-4 border border-border">
        <div class="aspect-square bg-muted rounded-lg mb-3 flex items-center justify-center">
          <span class="text-2xl text-muted-foreground">📷</span>
        </div>
        <p class="text-sm text-foreground">My post #{{ i }}</p>
        <div class="flex items-center justify-between text-xs text-muted-foreground mt-2">
          <span>{{ 20 + i * 5 }} likes</span>
          <span>{{ Math.floor(Math.random() * 30) + 1 }}d ago</span>
        </div>
      </div>
    </div>
  </div>
</template>