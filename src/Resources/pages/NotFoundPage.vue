<template>
  <div class="text-center space-y-6">
    <div class="text-9xl">😵</div>
    <div>
      <h1 class="text-4xl font-bold text-foreground">404</h1>
      <p class="text-xl text-muted-foreground mt-2">Page not found</p>
    </div>
    <p class="text-muted-foreground max-w-md mx-auto">
      The page you're looking for doesn't exist or has been moved.
    </p>
    <div class="flex justify-center space-x-4">
      <router-link 
        to="/"
        class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2"
      >
        Go Home
      </router-link>
      <button 
        @click="$router.go(-1)"
        class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2"
      >
        Go Back
      </button>
    </div>
  </div>
</template>