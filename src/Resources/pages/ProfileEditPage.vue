<script setup lang="ts">
import {computed, onMounted, reactive, ref} from 'vue'
import {useUserStore} from '@/stores/UserStore'
import {type IBreadcrumb, useBreadcrumbStore} from "@/stores/BreadcrumbStore.ts";
import {Separator} from '@/components/ui/separator';
import {Input} from "@/components/ui/input";
import {Label} from "@/components/ui/label";
import {Textarea} from "@/components/ui/textarea";
import {Button} from "@/components/ui/button";

const breadcrumbStore = useBreadcrumbStore();
const userStore = useUserStore()

const breadcrumbs: IBreadcrumb[] = [
  {name: 'Profile', path: '/profile'},
  {name: 'Edit Profile', path: '/profile/edit'}
]

breadcrumbStore.replaceBreadcrumbs(breadcrumbs);

const user = computed(() => userStore.getUser)
const profilePictureUrl = computed(() => user.value?.profilePicture)

// Form states
const updatingProfile = ref(false)
const changingPassword = ref(false)
const uploadingPicture = ref(false)

const profileForm = reactive({
  name: '',
  username: '',
  email: '',
  biography: ''
})

const passwordForm = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

const errors = ref<Record<string, string[]>>({})
const passwordErrors = ref<Record<string, string[]>>({})
const uploadErrors = ref<Record<string, string | string[]>>({})
const successMessage = ref<string>('')
const passwordSuccessMessage = ref<string>('')

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
  successMessage.value = ''

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
      if (data.fields) {
        errors.value = data.fields
      } else {
        throw new Error(data.message || 'Failed to update profile')
      }
      return
    }

    // Update user store with new data
    if (data.user) {
      userStore.setUserFromApiResponse(data.user)
    }

    // Show success message
    successMessage.value = 'Profile updated successfully!'

  } catch (error) {
    console.error('Profile update error:', error)
    errors.value = {general: ['Failed to update profile. Please try again.']}
  } finally {
    updatingProfile.value = false
  }
}

const changePassword = async () => {
  if (changingPassword.value) return

  changingPassword.value = true
  passwordErrors.value = {}
  passwordSuccessMessage.value = ''

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
      if (data.fields) {
        passwordErrors.value = data.fields
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
    passwordSuccessMessage.value = 'Password changed successfully!'

  } catch (error) {
    console.error('Password change error:', error)
    passwordErrors.value = {general: ['Failed to change password. Please try again.']}
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

<template>
  <div class="grid auto-rows-min gap-4 md:grid-cols-3">
    <div class="space-y-6 col-span-2">
      <div>
        <h3 class="text-lg font-medium">Profile</h3>
        <p class="text-sm text-muted-foreground">This is how others will see you on the site.</p>
      </div>
      <Separator/>
      <div class="grid gap-2">
        <Label for="name">Name</Label>
        <Input
            id="name"
            v-model="profileForm.name"
            type="text"
            placeholder="Your name"
            required
            :class="{ 'border-red-500': errors.name }"
            :aria-describedby="errors.name ? 'name-error' : undefined"
            :aria-invalid="!!errors.name"
        />
        <p class="text-sm text-muted-foreground">Your full name.</p>
        <p v-if="errors.name" id="name-error" class="text-red-500 text-sm" role="alert" aria-live="polite">
          {{ errors.name }}
        </p>
      </div>
      <div class="grid gap-2">
        <Label for="username">Username</Label>
        <Input
            id="username"
            v-model="profileForm.username"
            type="text"
            placeholder="Your username"
            required
            :class="{ 'border-red-500': errors.username }"
            :aria-describedby="errors.username ? 'username-error' : undefined"
            :aria-invalid="!!errors.username"
        />
        <p class="text-sm text-muted-foreground">Your unique username. This is the name other users can find you
          with.</p>
        <p v-if="errors.username" id="username-error" class="text-red-500 text-sm" role="alert" aria-live="polite">
          {{ errors.username }}
        </p>
      </div>
      <div class="grid gap-2">
        <Label for="email">Email</Label>
        <Input
            id="email"
            v-model="profileForm.email"
            type="email"
            placeholder="info@binsta.com"
            required
            :class="{ 'border-red-500': errors.email }"
            :aria-describedby="errors.email ? 'email-error' : undefined"
            :aria-invalid="!!errors.email"
        />
        <p class="text-sm text-muted-foreground">Your email address. This is used for account recovery and
          notifications.</p>
        <p v-if="errors.email" id="email-error" class="text-red-500 text-sm" role="alert" aria-live="polite">
          {{ errors.email }}
        </p>
      </div>
      <div class="grid gap-2">
        <Label for="profile-picture">Profile Picture</Label>
        <div class="flex items-center gap-4">
          <img
              v-if="profilePictureUrl"
              :src="profilePictureUrl"
              alt="Profile Picture"
              class="w-16 h-16 rounded-full object-cover"
              @error="handleImageError"
          />
          <div class="flex flex-col gap-2">
            <Input
                id="profile-picture"
                type="file"
                accept="image/*"
                @change="handleFileSelect"
            />
            <p class="text-sm text-muted-foreground">Upload a new profile picture. Recommended size is 256x256
              pixels.</p>
            <p v-if="uploadErrors.profile_picture" class="text-red-500 text-sm" role="alert" aria-live="polite">
              {{ uploadErrors.profile_picture }}
            </p>
          </div>
        </div>
      </div>
      <div class="grid gap-2">
        <Label for="biography">Biography</Label>
        <Textarea
            id="biography"
            v-model="profileForm.biography"
            placeholder="Tell us about yourself"
            rows="3"
            :class="{ 'border-red-500': errors.biography }"
            :aria-describedby="errors.biography ? 'biography-error' : undefined"
            :aria-invalid="!!errors.biography"
        />
        <p class="text-sm text-muted-foreground">A short bio about yourself. This will be displayed on your profile.</p>
        <p v-if="errors.biography" id="biography-error" class="text-red-500 text-sm" role="alert" aria-live="polite">
          {{ errors.biography }}
        </p>
      </div>
      <div class="flex justify-end items-center gap-4">
        <div class="flex flex-col gap-1">
          <p v-if="successMessage" class="text-green-600 text-sm" role="alert" aria-live="polite">
            {{ successMessage }}
          </p>
          <p v-if="errors.general" class="text-red-500 text-sm" role="alert" aria-live="polite">
            {{ errors.general[0] }}
          </p>
        </div>
        <Button
            :disabled="updatingProfile"
            @click="updateProfile"
        >
          <span v-if="updatingProfile">Updating...</span>
          <span v-else>Update Profile</span>
        </Button>
      </div>
      <Separator/>
      <div>
        <h4 class="text-md font-medium">Change Password</h4>
        <p class="text-sm text-muted-foreground">Keep your account secure by changing your password regularly.</p>
      </div>
      <div class="grid gap-2">
        <Label for="current_password">Current Password</Label>
        <Input
            id="current_password"
            v-model="passwordForm.current_password"
            type="password"
            placeholder="Current password"
            required
            :class="{ 'border-red-500': passwordErrors.current_password }"
            :aria-describedby="passwordErrors.current_password ? 'current-password-error' : undefined"
            :aria-invalid="!!passwordErrors.current_password"
        />
        <p
            v-if="passwordErrors.current_password"
            id="current-password-error"
            class="text-red-500 text-sm"
            role="alert"
            aria-live="polite"
        >
          {{ passwordErrors.current_password }}
        </p>
      </div>
      <div class="grid gap-2">
        <Label for="new_password">New Password</Label>
        <Input
            id="new_password"
            v-model="passwordForm.new_password"
            type="password"
            placeholder="New password"
            required
            :class="{ 'border-red-500': passwordErrors.new_password }"
            :aria-describedby="passwordErrors.new_password ? 'new-password-error' : undefined"
            :aria-invalid="!!passwordErrors.new_password"
        />
        <p
            v-if="passwordErrors.new_password"
            id="new-password-error"
            class="text-red-500 text-sm"
            role="alert"
            aria-live="polite"
        >
          {{ passwordErrors.new_password }}
        </p>
      </div>
      <div class="grid gap-2">
        <Label for="new_password_confirmation">Confirm New Password</Label>
        <Input
            id="new_password_confirmation"
            v-model="passwordForm.new_password_confirmation"
            type="password"
            placeholder="Confirm new password"
            required
            :class="{ 'border-red-500': passwordErrors.new_password_confirmation }"
            :aria-describedby="passwordErrors.new_password_confirmation ? 'new-password-confirmation-error' : undefined"
            :aria-invalid="!!passwordErrors.new_password_confirmation"
        />
        <p
            v-if="passwordErrors.new_password_confirmation"
            id="new-password-confirmation-error"
            class="text-red-500 text-sm"
            role="alert"
            aria-live="polite"
        >
          {{ passwordErrors.new_password_confirmation }}
        </p>
      </div>
      <div class="flex justify-end items-center gap-4">
        <div class="flex flex-col gap-1">
          <p v-if="passwordSuccessMessage" class="text-green-600 text-sm" role="alert" aria-live="polite">
            {{ passwordSuccessMessage }}
          </p>
          <p v-if="passwordErrors.general" class="text-red-500 text-sm" role="alert" aria-live="polite">
            {{ passwordErrors.general[0] }}
          </p>
        </div>
        <Button
            :disabled="changingPassword"
            @click="changePassword"
        >
          <span v-if="changingPassword">Changing...</span>
          <span v-else>Change Password</span>
        </Button>
      </div>
    </div>
  </div>
</template>