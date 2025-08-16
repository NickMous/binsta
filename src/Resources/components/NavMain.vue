<script setup lang="ts">
import {ChevronRight, type LucideIcon} from 'lucide-vue-next'
import {useUserStore} from '@/stores/UserStore.ts'
import {Collapsible, CollapsibleContent, CollapsibleTrigger,} from '@/components/ui/collapsible'
import {
  SidebarGroup,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubButton,
  SidebarMenuSubItem,
} from '@/components/ui/sidebar'

defineProps<{
  items: {
    title: string
    url: string
    icon?: LucideIcon
    isActive?: boolean
    items?: {
      title: string
      url: string
    }[]
  }[]
}>()

const userStore = useUserStore()
</script>

<template>
  <SidebarGroup>
    <SidebarGroupLabel>Navigation</SidebarGroupLabel>
    <SidebarMenu>
      <template v-for="item in items" :key="item.title">
        <!-- Skip Personal Feed if user is not logged in -->
        <template v-if="item.title === 'Personal Feed' && userStore.getUsername === ''">
          <!-- Don't render Personal Feed for guests -->
        </template>
        <template v-else>
          <!-- Items with sub-items (collapsible) -->
          <Collapsible
            v-if="item.items && item.items.length > 0"
            as-child
            :default-open="item.isActive"
            class="group/collapsible"
          >
            <SidebarMenuItem>
              <CollapsibleTrigger as-child>
                <SidebarMenuButton :tooltip="item.title">
                  <component :is="item.icon" v-if="item.icon" />
                  <span>{{ item.title }}</span>
                  <ChevronRight class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                </SidebarMenuButton>
              </CollapsibleTrigger>
              <CollapsibleContent>
                <SidebarMenuSub>
                  <SidebarMenuSubItem v-for="subItem in item.items" :key="subItem.title">
                    <SidebarMenuSubButton as-child>
                      <RouterLink :to="subItem.url">
                        <span>{{ subItem.title }}</span>
                      </RouterLink>
                    </SidebarMenuSubButton>
                  </SidebarMenuSubItem>
                </SidebarMenuSub>
              </CollapsibleContent>
            </SidebarMenuItem>
          </Collapsible>
          
          <!-- Items without sub-items (simple links) -->
          <SidebarMenuItem v-else>
            <SidebarMenuButton as-child :tooltip="item.title">
              <RouterLink :to="item.url">
                <component :is="item.icon" v-if="item.icon" />
                <span>{{ item.title }}</span>
              </RouterLink>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </template>
      </template>
    </SidebarMenu>
  </SidebarGroup>
</template>
