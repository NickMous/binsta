<script setup lang="ts">
import {useRoute} from "vue-router";
import {ref, onMounted} from "vue";
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
const userStats = ref<{followers_count: number, following_count: number} | null>(null);
const isLoading = ref(true);

async function fetchUserData() {
  try {
    isLoading.value = true;
    const response = await fetch(`/api/users/${username}`);
    
    if (!response.ok) {
      throw new Error("User not found");
    }
    
    const data = await response.json();
    userData.value = User.fromApiResponse(data as UserApiResponse);
    
    await Promise.all([
      fetchUserFollowStatus(),
      fetchUserStatistics()
    ]);
  } catch (error) {
    console.error("Error fetching user data:", error);
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  fetchUserData();
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

async function fetchUserStatistics() {
  try {
    const response = await fetch(`/api/users/${username}/statistics`);

    if (!response.ok) {
      throw new Error("Failed to fetch user statistics");
    }

    const data = await response.json();
    userStats.value = data;
  } catch (error) {
    console.error("Error fetching user statistics:", error);
  }
}

async function followUser() {
  if (userData.value) {
    userStore.followUser(userData.value.id)
        .then(() => {
          followsUser.value = true;
          fetchUserStatistics(); // Refresh stats after following
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
          fetchUserStatistics(); // Refresh stats after unfollowing
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
        <transition name="button-change" mode="out-in">
          <Button
              v-if="userData && userData.id !== userStore.getId && !followsUser"
              type="button"
              class="hidden sm:block transition-all duration-200 hover:scale-105 active:scale-95"
              variant="default"
              @click="followUser"
          >
            + Follow
          </Button>
          <Button
              v-else-if="userData && userData.id !== userStore.getId && followsUser"
              type="button"
              class="hidden sm:block transition-all duration-200 hover:scale-105 active:scale-95"
              variant="destructive"
              @click="unfollowUser"
          >
            - Unfollow
          </Button>
        </transition>
      </div>
      <p class="text-gray-500">{{ username }}</p>
    </div>
    
    <!-- User Statistics -->
    <div class="col-span-3 flex justify-center w-full">
      <div class="flex gap-8">
        <div class="text-center group cursor-pointer">
          <div v-if="userStats === null">
            <Skeleton class="h-6 w-12 mb-1"/>
          </div>
          <div v-else>
            <transition name="stat-update" mode="out-in">
              <div 
                :key="userStats.followers_count"
                class="text-xl font-bold transition-all duration-300 group-hover:scale-110 group-hover:text-blue-600"
              >
                {{ userStats.followers_count }}
              </div>
            </transition>
          </div>
          <div class="text-sm text-gray-500 transition-colors duration-300 group-hover:text-blue-500">Followers</div>
        </div>
        <div class="text-center group cursor-pointer">
          <div v-if="userStats === null">
            <Skeleton class="h-6 w-12 mb-1"/>
          </div>
          <div v-else>
            <transition name="stat-update" mode="out-in">
              <div 
                :key="userStats.following_count"
                class="text-xl font-bold transition-all duration-300 group-hover:scale-110 group-hover:text-green-600"
              >
                {{ userStats.following_count }}
              </div>
            </transition>
          </div>
          <div class="text-sm text-gray-500 transition-colors duration-300 group-hover:text-green-500">Following</div>
        </div>
      </div>
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
      <transition name="button-change" mode="out-in">
        <Button
            v-if="userData && userData.id !== userStore.getId && !followsUser"
            type="button"
            class="w-full max-w-xs transition-all duration-200 hover:scale-105 active:scale-95"
            variant="default"
            @click="followUser"
        >
          + Follow
        </Button>
        <Button
            v-else-if="userData && userData.id !== userStore.getId && followsUser"
            type="button"
            class="w-full max-w-xs transition-all duration-200 hover:scale-105 active:scale-95"
            variant="destructive"
            @click="unfollowUser"
        >
          - Unfollow
        </Button>
      </transition>
    </div>
  </div>
</template>

<style scoped>
@reference '@/style.css';

/* Button transition animations */
.button-change-enter-active,
.button-change-leave-active {
  @apply transition-all duration-300 ease-in-out;
}

.button-change-enter-from {
  @apply opacity-0 scale-75 -translate-y-2;
}

.button-change-leave-to {
  @apply opacity-0 scale-75 translate-y-2;
}

.button-change-enter-to,
.button-change-leave-from {
  @apply opacity-100 scale-100 translate-y-0;
}

/* Statistics update animations */
.stat-update-enter-active,
.stat-update-leave-active {
  @apply transition-all duration-400 ease-in-out;
}

.stat-update-enter-from {
  @apply opacity-0 scale-110;
  transform: rotateY(90deg);
}

.stat-update-leave-to {
  @apply opacity-0 scale-90;
  transform: rotateY(-90deg);
}

.stat-update-enter-to,
.stat-update-leave-from {
  @apply opacity-100 scale-100;
  transform: rotateY(0deg);
}
</style>