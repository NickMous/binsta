<template>
  <div class="md:col-span-3 max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Edit Profile</h1>
      
      <!-- Profile Picture Section -->
      <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Profile Picture</h2>
        <div class="flex items-center space-x-6">
          <div class="relative">
            <div v-if="!profilePictureUrl" class="w-20 h-20 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center border-2 border-gray-200 dark:border-gray-600">
              <span class="text-xl font-medium text-gray-600 dark:text-gray-300">
                {{ (user?.name || 'U').charAt(0).toUpperCase() }}
              </span>
            </div>
            <img 
              v-else
              :src="profilePictureUrl" 
              :alt="user?.name || 'Profile picture'"
              class="w-20 h-20 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600"
              @error="handleImageError"
            >
          </div>
          <div>
            <input 
              ref="fileInput"
              type="file" 
              accept="image/*" 
              class="hidden"
              @change="handleFileSelect"
            >
            <button
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
              :disabled="uploadingPicture"
              @click="$refs.fileInput?.click()"
            >
              {{ uploadingPicture ? 'Uploading...' : 'Change Picture' }}
            </button>
            <div class="mt-1">
              <p class="text-sm text-gray-500 dark:text-gray-400">
                JPG, PNG, GIF, or WebP. Max 2MB.
              </p>
              <p v-if="uploadErrors.profile_picture" class="text-sm text-red-600 mt-1">
                {{ Array.isArray(uploadErrors.profile_picture) ? uploadErrors.profile_picture[0] : uploadErrors.profile_picture }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Profile Form -->
      <form class="space-y-6" @submit.prevent="updateProfile">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Display Name
          </label>
          <input
            id="name"
            v-model="profileForm.name"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
            :class="{ 'border-red-500': errors.name }"
          >
          <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
        </div>

        <div>
          <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Username
          </label>
          <input
            id="username"
            v-model="profileForm.username"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
            :class="{ 'border-red-500': errors.username }"
          >
          <p v-if="errors.username" class="mt-1 text-sm text-red-600">{{ errors.username[0] }}</p>
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Email Address
          </label>
          <input
            id="email"
            v-model="profileForm.email"
            type="email"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
            :class="{ 'border-red-500': errors.email }"
          >
          <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
        </div>

        <div>
          <label for="biography" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Biography
          </label>
          <textarea
            id="biography"
            v-model="profileForm.biography"
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white resize-none"
            :class="{ 'border-red-500': errors.biography }"
            placeholder="Tell people about yourself..."
            maxlength="500"
          ></textarea>
          <div class="flex justify-between mt-1">
            <p v-if="errors.biography" class="text-sm text-red-600">{{ errors.biography[0] }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              {{ (profileForm.biography || '').length }}/500
            </p>
          </div>
        </div>

        <div class="flex justify-end space-x-3">
          <button
            type="button"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            @click="$router.push('/')"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="updatingProfile"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            {{ updatingProfile ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>

      <!-- Change Password Section -->
      <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-600">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Change Password</h2>
        <form class="space-y-4" @submit.prevent="changePassword">
          <div>
            <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Current Password
            </label>
            <input
              id="current_password"
              v-model="passwordForm.current_password"
              type="password"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
              :class="{ 'border-red-500': passwordErrors.current_password }"
            >
            <p v-if="passwordErrors.current_password" class="mt-1 text-sm text-red-600">{{ passwordErrors.current_password[0] }}</p>
          </div>

          <div>
            <label for="new_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              New Password
            </label>
            <input
              id="new_password"
              v-model="passwordForm.new_password"
              type="password"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
              :class="{ 'border-red-500': passwordErrors.new_password }"
            >
            <p v-if="passwordErrors.new_password" class="mt-1 text-sm text-red-600">{{ passwordErrors.new_password[0] }}</p>
          </div>

          <div>
            <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Confirm New Password
            </label>
            <input
              id="new_password_confirmation"
              v-model="passwordForm.new_password_confirmation"
              type="password"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
              :class="{ 'border-red-500': passwordErrors.new_password_confirmation }"
            >
            <p v-if="passwordErrors.new_password_confirmation" class="mt-1 text-sm text-red-600">{{ passwordErrors.new_password_confirmation[0] }}</p>
          </div>

          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="changingPassword"
              class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {{ changingPassword ? 'Changing...' : 'Change Password' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useUserStore } from '@/stores/UserStore'

const userStore = useUserStore()

const user = computed(() => userStore.getUser)
const profilePictureUrl = computed(() => user.value?.profilePicture)

// Form states
const updatingProfile = ref(false)
const changingPassword = ref(false)
const uploadingPicture = ref(false)

// Profile form
const profileForm = reactive({
  name: '',
  username: '',
  email: '',
  biography: ''
})

// Password form
const passwordForm = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

// Error states
const errors = ref<Record<string, string[]>>({})
const passwordErrors = ref<Record<string, string[]>>({})
const uploadErrors = ref<Record<string, string | string[]>>({})

// Initialize form with user data
onMounted(() => {
  if (user.value) {
    profileForm.name = user.value.name || ''
    profileForm.username = user.value.username || ''
    profileForm.email = user.value.email || ''
    profileForm.biography = user.value.biography || ''
  }
})

const updateProfile = async () => {
  if (updatingProfile.value) return

  updatingProfile.value = true
  errors.value = {}

  try {
    const response = await fetch('/api/profile', {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(profileForm)
    })

    const data = await response.json()

    if (!response.ok) {
      if (data.errors) {
        errors.value = data.errors
      } else {
        throw new Error(data.message || 'Failed to update profile')
      }
      return
    }

    // Update user store with new data
    if (data.user) {
      userStore.setUserFromApiResponse(data.user)
    }

    // Show success message (you might want to add a toast notification here)
    alert('Profile updated successfully!')
    
  } catch (error) {
    console.error('Profile update error:', error)
    alert('Failed to update profile. Please try again.')
  } finally {
    updatingProfile.value = false
  }
}

const changePassword = async () => {
  if (changingPassword.value) return

  changingPassword.value = true
  passwordErrors.value = {}

  try {
    const response = await fetch('/api/profile/password', {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(passwordForm)
    })

    const data = await response.json()

    if (!response.ok) {
      if (data.errors) {
        passwordErrors.value = data.errors
      } else {
        throw new Error(data.message || 'Failed to change password')
      }
      return
    }

    // Clear password form
    passwordForm.current_password = ''
    passwordForm.new_password = ''
    passwordForm.new_password_confirmation = ''

    // Show success message
    alert('Password changed successfully!')
    
  } catch (error) {
    console.error('Password change error:', error)
    alert('Failed to change password. Please try again.')
  } finally {
    changingPassword.value = false
  }
}

const handleImageError = (event: Event) => {
  // Hide the broken image and show the fallback
  const target = event.target as HTMLImageElement
  target.style.display = 'none'
}

const handleFileSelect = async (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  
  if (!file) return

  uploadingPicture.value = true
  uploadErrors.value = {} // Clear previous errors

  try {
    const formData = new FormData()
    formData.append('profile_picture', file)

    const response = await fetch('/api/profile/picture', {
      method: 'POST',
      body: formData
    })

    const data = await response.json()

    if (!response.ok) {
      // Handle validation errors
      if (data.errors || data.fields) {
        uploadErrors.value = data.errors || data.fields
      } else {
        uploadErrors.value = { profile_picture: data.message || 'Failed to upload profile picture' }
      }
      return
    }

    // Update user store with new data
    if (data.user) {
      userStore.setUserFromApiResponse(data.user)
    }

    // Clear any previous errors on success
    uploadErrors.value = {}
    
  } catch (error) {
    console.error('Profile picture upload error:', error)
    uploadErrors.value = { profile_picture: 'Network error. Please check your connection and try again.' }
  } finally {
    uploadingPicture.value = false
    // Clear file input
    target.value = ''
  }
}
</script>