<script setup lang="ts">
import {useRoute} from "vue-router";
import {ref} from "vue";
import {Avatar, AvatarFallback} from "@/components/ui/avatar";
import {User, type UserApiResponse} from "@/entities/User.ts";
import {Skeleton} from "@/components/ui/skeleton";
import {Button} from "@/components/ui/button";
import {useUserStore} from "@/stores/UserStore.ts";

const route = useRoute()
const userStore = useUserStore();
const username = route.params.username as string;

const userData = ref<User | null>(null);
const followsUser = ref(false);

fetch(`/api/users/${username}`)
    .then(response => {
      if (!response.ok) {
        throw new Error("User not found");
      }
      return response.json();
    })
    .then(data => {
      userData.value = User.fromApiResponse(data as UserApiResponse);
      fetchUserFollowStatus();
    })
    .catch(error => {
      console.error("Error fetching user data:", error);
    });

async function fetchUserFollowStatus() {
  try {
    const response = await fetch(`/api/users/${userData.value?.id}/follow-status`);

    if (!response.ok) {
      throw new Error("Failed to fetch follow status");
    }

    const data = await response.json();
    followsUser.value = data.isFollowing;
  } catch (error) {
    console.error("Error fetching follow status:", error);
  }
}

async function followUser() {
  if (userData.value) {
    userStore.followUser(userData.value.id)
        .then(() => {
          followsUser.value = true;
        })
        .catch(error => {
          console.error("Error following user:", error);
        });
  }
}

async function unfollowUser() {
  if (userData.value) {
    userStore.unfollowUser(userData.value.id)
        .then(() => {
          followsUser.value = false;
        })
        .catch(error => {
          console.error("Error unfollowing user:", error);
        });
  }
}
</script>

<template>
  <div class="grid auto-rows-min gap-4 md:grid-cols-3">
    <div class="flex justify-end items-center w-full">
      <Skeleton v-if="userData === null" class="relative flex shrink-0 overflow-hidden rounded-full size-20"/>
      <Avatar v-else-if="userData" class="size-20">
        <AvatarFallback class="text-4xl">{{ userData.name.charAt(0).toUpperCase() }}</AvatarFallback>
      </Avatar>
    </div>
    <div class="col-span-2 justify-items-start content-center">
      <div class="flex flex-col sm:flex-row sm:items-center sm:gap-4">
        <div>
          <Skeleton v-if="userData === null" class="h-8 w-2/3"/>
          <h1 v-else class="text-2xl font-bold">{{ userData.getDisplayName() }}</h1>
        </div>
        <!-- Desktop follow button -->
        <Button
            v-if="userData && userData.id !== userStore.getId && !followsUser"
            type="button"
            class="hidden sm:block"
            variant="default"
            @click="followUser"
        >
          + Follow
        </Button>
        <Button
            v-else-if="userData && userData.id !== userStore.getId && followsUser"
            type="button"
            class="hidden sm:block"
            variant="destructive"
            @click="unfollowUser"
        >
          - Unfollow
        </Button>
      </div>
      <p class="text-gray-500">{{ username }}</p>
    </div>
    <div class="col-span-3 flex justify-center w-full">
      <div class="md:w-1/3">
        <Skeleton v-if="userData === null" class="h-8 mx-1 w-full"/>
        <p v-else class="text-gray-700 mx-1">
          {{ userData.biography || "This user has not set a bio yet." }}
        </p>
      </div>
    </div>
    
    <!-- Mobile follow button -->
    <div class="col-span-3 flex justify-center w-full sm:hidden">
      <Button
          v-if="userData && userData.id !== userStore.getId && !followsUser"
          type="button"
          class="w-full max-w-xs"
          variant="default"
          @click="followUser"
      >
        + Follow
      </Button>
      <Button
          v-else-if="userData && userData.id !== userStore.getId && followsUser"
          type="button"
          class="w-full max-w-xs"
          variant="destructive"
          @click="unfollowUser"
      >
        - Unfollow
      </Button>
    </div>
  </div>
</template>

<style scoped>

</style>