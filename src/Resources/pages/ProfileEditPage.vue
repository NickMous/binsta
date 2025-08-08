<template>
    <div class=""></div>
</template>

<script setup lang="ts">
import {computed, onMounted, reactive, ref} from 'vue'
import {useUserStore} from '@/stores/UserStore'

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
        uploadErrors.value = {profile_picture: data.message || 'Failed to upload profile picture'}
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
    uploadErrors.value = {profile_picture: 'Network error. Please check your connection and try again.'}
  } finally {
    uploadingPicture.value = false
    // Clear file input
    target.value = ''
  }
}
</script>