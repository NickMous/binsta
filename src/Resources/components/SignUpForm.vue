<script setup lang="ts">
import type {HTMLAttributes} from 'vue'
import {ref} from 'vue'
import {useRouter} from 'vue-router'

import {GalleryVerticalEnd} from 'lucide-vue-next'
import {cn} from '@/lib/utils'
import {Button} from '@/components/ui/button'
import {Input} from '@/components/ui/input'
import {Label} from '@/components/ui/label'
import {useUserStore} from '@/stores/UserStore'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()

const router = useRouter()
const userStore = useUserStore()

const name = ref('')
const username = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const isLoading = ref(false)
const error = ref('')
const errors = ref<Record<string, string>>({})

const handleSignUp = async (event: Event) => {
  event.preventDefault()
  isLoading.value = true
  error.value = ''
  errors.value = {}

  // Client-side validation for password confirmation
  if (password.value !== confirmPassword.value) {
    errors.value.confirmPassword = 'Passwords do not match'
    isLoading.value = false
    return
  }

  try {
    const response = await fetch('/api/auth/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        name: name.value,
        username: username.value,
        email: email.value,
        password: password.value,
        password_confirmation: confirmPassword.value,
      }),
    })

    const data = await response.json()

    if (!response.ok) {
      if (data.fields) {
        errors.value = data.fields
      } else {
        error.value = data.message || 'Sign up failed'
      }
      return
    }

    // Handle successful sign up - save user in store and redirect
    console.log('Sign up successful:', data)
    
    // Save user data to store
    if (data.user) {
      userStore.setUserFromApiResponse(data.user)
      userStore.persistUser()
    }
    
    // Redirect to home page
    await router.push('/')

  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Sign up failed'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div :class="cn('flex flex-col gap-6', props.class)">
    <form @submit="handleSignUp">
      <div class="flex flex-col gap-6">
        <div class="flex flex-col items-center gap-2">
          <RouterLink to="/" class="flex flex-col items-center gap-2 font-medium">
            <div class="flex size-8 items-center justify-center rounded-md">
              <GalleryVerticalEnd class="size-6"/>
            </div>
            <span class="sr-only">Binsta</span>
          </RouterLink>
          <h1 class="text-xl font-bold">
            Create your account
          </h1>
          <div class="text-center text-sm">
            Already have an account?
            <RouterLink to="/login" class="underline underline-offset-4">
              Sign in
            </RouterLink>
          </div>
        </div>
        <div class="flex flex-col gap-6">
          <div v-if="error" class="text-red-500 text-sm text-center" role="alert" aria-live="polite">
            {{ error }}
          </div>
          <div class="grid gap-3">
            <Label for="name">Name</Label>
            <Input
                id="name"
                v-model="name"
                type="text"
                placeholder="John Doe"
                required
                :class="{ 'border-red-500': errors.name }"
                :aria-describedby="errors.name ? 'name-error' : undefined"
                :aria-invalid="!!errors.name"
            />
            <div v-if="errors.name" id="name-error" class="text-red-500 text-sm" role="alert" aria-live="polite">
              {{ errors.name }}
            </div>
          </div>
          <div class="grid gap-3">
            <Label for="username">Username</Label>
            <Input
                id="username"
                v-model="username"
                type="text"
                placeholder="johndoe"
                required
                :class="{ 'border-red-500': errors.username }"
                :aria-describedby="errors.username ? 'username-error' : undefined"
                :aria-invalid="!!errors.username"
            />
            <div v-if="errors.username" id="username-error" class="text-red-500 text-sm" role="alert" aria-live="polite">
              {{ errors.username }}
            </div>
          </div>
          <div class="grid gap-3">
            <Label for="email">Email</Label>
            <Input
                id="email"
                v-model="email"
                type="email"
                placeholder="m@example.com"
                required
                :class="{ 'border-red-500': errors.email }"
                :aria-describedby="errors.email ? 'email-error' : undefined"
                :aria-invalid="!!errors.email"
            />
            <div v-if="errors.email" id="email-error" class="text-red-500 text-sm" role="alert" aria-live="polite">
              {{ errors.email }}
            </div>
          </div>
          <div class="grid gap-3">
            <Label for="password">Password</Label>
            <Input
                id="password"
                v-model="password"
                type="password"
                placeholder="••••••••"
                required
                :class="{ 'border-red-500': errors.password }"
                :aria-describedby="errors.password ? 'password-error' : undefined"
                :aria-invalid="!!errors.password"
            />
            <div v-if="errors.password" id="password-error" class="text-red-500 text-sm" role="alert" aria-live="polite">
              {{ errors.password }}
            </div>
          </div>
          <div class="grid gap-3">
            <Label for="confirmPassword">Confirm Password</Label>
            <Input
                id="confirmPassword"
                v-model="confirmPassword"
                type="password"
                placeholder="••••••••"
                required
                :class="{ 'border-red-500': errors.confirmPassword }"
                :aria-describedby="errors.confirmPassword ? 'confirmPassword-error' : undefined"
                :aria-invalid="!!errors.confirmPassword"
            />
            <div v-if="errors.confirmPassword" id="confirmPassword-error" class="text-red-500 text-sm" role="alert" aria-live="polite">
              {{ errors.confirmPassword }}
            </div>
          </div>
          <Button type="submit" class="w-full" :disabled="isLoading">
            {{ isLoading ? 'Creating account...' : 'Create account' }}
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