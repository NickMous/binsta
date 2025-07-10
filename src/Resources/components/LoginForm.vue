<script setup lang="ts">
import type {HTMLAttributes} from 'vue'
import {ref} from 'vue'

import {GalleryVerticalEnd} from 'lucide-vue-next'
import {cn} from '@/lib/utils'
import {Button} from '@/components/ui/button'
import {Input} from '@/components/ui/input'
import {Label} from '@/components/ui/label'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()

const email = ref('')
const password = ref('')
const isLoading = ref(false)
const error = ref('')

const handleLogin = async (event: Event) => {
  event.preventDefault()
  isLoading.value = true
  error.value = ''

  try {
    const response = await fetch('/api/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        email: email.value,
        password: password.value,
      }),
    })

    if (!response.ok) {
      throw new Error('Login failed')
    }

    const data = await response.json()

    // Handle successful login (e.g., redirect or update auth state)
    console.log('Login successful:', data)

  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Login failed'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div :class="cn('flex flex-col gap-6', props.class)">
    <form @submit="handleLogin">
      <div class="flex flex-col gap-6">
        <div class="flex flex-col items-center gap-2">
          <RouterLink to="/" class="flex flex-col items-center gap-2 font-medium">
            <div class="flex size-8 items-center justify-center rounded-md">
              <GalleryVerticalEnd class="size-6"/>
            </div>
            <span class="sr-only">Binsta</span>
          </RouterLink>
          <h1 class="text-xl font-bold">
            Welcome to Binsta!
          </h1>
          <div class="text-center text-sm">
            Don't have an account?
            <a href="#" class="underline underline-offset-4">
              Sign up
            </a>
          </div>
        </div>
        <div class="flex flex-col gap-6">
          <div v-if="error" class="text-red-500 text-sm text-center">
            {{ error }}
          </div>
          <div class="grid gap-3">
            <Label for="email">Email</Label>
            <Input
                id="email"
                v-model="email"
                type="email"
                placeholder="m@example.com"
                required
            />
          </div>
          <div class="grid gap-3">
            <Label for="password">Password</Label>
            <Input
                id="password"
                v-model="password"
                type="password"
                placeholder="••••••••"
                required
            />
          </div>
          <Button type="submit" class="w-full" :disabled="isLoading">
            {{ isLoading ? 'Logging in...' : 'Login' }}
          </Button>
        </div>
      </div>
    </form>
    <div
        class="text-muted-foreground *:[a]:hover:text-primary text-center text-xs text-balance *:[a]:underline *:[a]:underline-offset-4">
      By clicking continue, you agree to our <a href="#">Terms of Service</a>
      and <a href="#">Privacy Policy</a>.
    </div>
  </div>
</template>
