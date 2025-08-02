<script setup lang="ts">
import {BadgeCheck, Bell, ChevronsUpDown, CreditCard, LogIn, LogOut, Sparkles} from 'lucide-vue-next'

import {Avatar, AvatarFallback, AvatarImage} from '@/components/ui/avatar'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar,} from '@/components/ui/sidebar'
import {useUserStore} from "@/stores/UserStore.ts";

const {isMobile} = useSidebar()
const userStore = useUserStore()
</script>

<template>
  <SidebarMenu>
    <SidebarMenuItem>
      <DropdownMenu>
        <DropdownMenuTrigger
            v-if="userStore.getUsername === ''"
            as-child
        >
          <SidebarMenuButton
              size="lg"
              class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
          >
            <Avatar class="h-8 w-8 rounded-lg">
              <AvatarImage :src="userStore.getProfilePicture ?? ''" :alt="userStore.getName"/>
              <AvatarFallback class="rounded-lg">
                GU
              </AvatarFallback>
            </Avatar>
            <div class="grid flex-1 text-left text-sm leading-tight">
              <span class="truncate font-medium">Guest</span>
              <span class="truncate text-xs">Just a lurker :)</span>
            </div>
            <ChevronsUpDown class="ml-auto size-4"/>
          </SidebarMenuButton>
        </DropdownMenuTrigger>
        <DropdownMenuTrigger
            v-if="userStore.getUsername !== ''"
            as-child
        >
          <SidebarMenuButton
              size="lg"
              class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
          >
            <Avatar class="h-8 w-8 rounded-lg">
              <AvatarImage :src="userStore.getProfilePicture ?? ''" :alt="userStore.getName"/>
              <AvatarFallback class="rounded-lg">
                {{ userStore.getName.charAt(0).toUpperCase() }}
              </AvatarFallback>
            </Avatar>
            <div class="grid flex-1 text-left text-sm leading-tight">
              <span class="truncate font-medium">{{ userStore.getName }}</span>
              <span class="truncate text-xs">{{ userStore.getEmail }}</span>
            </div>
            <ChevronsUpDown class="ml-auto size-4"/>
          </SidebarMenuButton>
        </DropdownMenuTrigger>
        <DropdownMenuContent
            v-if="userStore.getUsername === ''"
            class="w-[--reka-dropdown-menu-trigger-width] min-w-56 rounded-lg"
            :side="isMobile ? 'bottom' : 'right'"
            align="end"
            :side-offset="4"
        >
          <RouterLink to="/login">
            <DropdownMenuItem>
              <LogIn/>
              Log in
            </DropdownMenuItem>
          </RouterLink>
          <RouterLink to="/signup">
            <DropdownMenuItem>
              <Sparkles/>
              Sign up
            </DropdownMenuItem>
          </RouterLink>
        </DropdownMenuContent>
        <DropdownMenuContent
            v-if="userStore.getUsername !== ''"
            class="w-[--reka-dropdown-menu-trigger-width] min-w-56 rounded-lg"
            :side="isMobile ? 'bottom' : 'right'"
            align="end"
            :side-offset="4"
        >
          <DropdownMenuLabel class="p-0 font-normal">
            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
              <Avatar class="h-8 w-8 rounded-lg">
                <AvatarImage :src="userStore.getProfilePicture ?? ''" :alt="userStore.getName"/>
                <AvatarFallback class="rounded-lg">
                  CN
                </AvatarFallback>
              </Avatar>
              <div class="grid flex-1 text-left text-sm leading-tight">
                <span class="truncate font-semibold">{{ userStore.getName }}</span>
                <span class="truncate text-xs">{{ userStore.getEmail }}</span>
              </div>
            </div>
          </DropdownMenuLabel>
          <DropdownMenuSeparator/>
          <DropdownMenuGroup>
            <DropdownMenuItem>
              <Sparkles/>
              Upgrade to Pro
            </DropdownMenuItem>
          </DropdownMenuGroup>
          <DropdownMenuSeparator/>
          <DropdownMenuGroup>
            <DropdownMenuItem>
              <BadgeCheck/>
              Account
            </DropdownMenuItem>
            <DropdownMenuItem>
              <CreditCard/>
              Billing
            </DropdownMenuItem>
            <DropdownMenuItem>
              <Bell/>
              Notifications
            </DropdownMenuItem>
          </DropdownMenuGroup>
          <DropdownMenuSeparator/>
          <DropdownMenuItem>
            <LogOut/>
            Log out
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </SidebarMenuItem>
  </SidebarMenu>
</template>
