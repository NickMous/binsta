<script setup lang="ts">
import {GalleryVerticalEnd} from 'lucide-vue-next'
import {DropdownMenu} from '@/components/ui/dropdown-menu'

import {SidebarMenu, SidebarMenuButton, SidebarMenuItem,} from '@/components/ui/sidebar'
</script>

<template>
  <SidebarMenu>
    <SidebarMenuItem>
      <DropdownMenu>
        <RouterLink to="/">
          <SidebarMenuButton
            size="lg"
            class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
          >
            <div class="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
              <component :is="GalleryVerticalEnd" class="size-4" />
            </div>
            <div class="grid flex-1 text-left text-sm leading-tight">
              <span class="truncate font-medium">
                Binsta
              </span>
              <span class="truncate text-xs">The dev insta</span>
            </div>
          </SidebarMenuButton>
        </RouterLink>
      </DropdownMenu>
    </SidebarMenuItem>
  </SidebarMenu>
</template>
