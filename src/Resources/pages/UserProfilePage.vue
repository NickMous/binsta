<script setup lang="ts">
import {useRoute} from "vue-router";
import {ref} from "vue";
import {Avatar, AvatarFallback} from "@/components/ui/avatar";
import {User, type UserApiResponse} from "@/entities/User.ts";
import {Skeleton} from "@/components/ui/skeleton";

const route = useRoute()
const username = route.params.username as string;

const userData = ref<User | null>(null);

// const timeout = setTimeout(() => {
//   fetch(`/api/users/${username}`)
//       .then(response => {
//         if (!response.ok) {
//           throw new Error("User not found");
//         }
//         return response.json();
//       })
//       .then(data => {
//         // Handle user data
//         userData.value = User.fromApiResponse(data as UserApiResponse);
//       })
//       .catch(error => {
//         console.error("Error fetching user data:", error);
//       });
//   clearTimeout(timeout);
// }, 5000);

fetch(`/api/users/${username}`)
    .then(response => {
      if (!response.ok) {
        throw new Error("User not found");
      }
      return response.json();
    })
    .then(data => {
      // Handle user data
      userData.value = User.fromApiResponse(data as UserApiResponse);
    })
    .catch(error => {
      console.error("Error fetching user data:", error);
    });
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
      <Skeleton v-if="userData === null" class="h-8 w-2/3"/>
      <h1 v-else class="text-2xl font-bold text-center">{{ userData.getDisplayName() }}</h1>
      <p class="text-center text-gray-500">{{ username }}</p>
    </div>
    <div class="col-span-3 flex justify-center w-full">
      <div class="w-1/3">
        <Skeleton v-if="userData === null" class="h-8 mx-1 w-full"/>
        <p v-else class="text-gray-700 mx-1">
          {{ userData.biography || "This user has not set a bio yet." }}
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>