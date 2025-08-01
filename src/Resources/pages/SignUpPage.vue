<script lang="ts">
export const description = 'A simple sign up page for new users.'
</script>

<script setup lang="ts">
import SignUpForm from '@/components/SignUpForm.vue'
</script>

<template>
  <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
    <div class="w-full max-w-sm">
      <SignUpForm />
    </div>
  </div>
</template>