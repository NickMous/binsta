<script setup lang="ts">
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  type SidebarProps,
  SidebarRail
} from '@/components/ui/sidebar'

import {Home, Plus, Users} from 'lucide-vue-next'
import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import TeamSwitcher from '@/components/TeamSwitcher.vue'
import SearchBar from "@/components/SearchBar.vue";

const props = withDefaults(defineProps<SidebarProps>(), {
  collapsible: 'icon',
})

const data = {
  navMain: [
    {
      title: 'Home',
      url: '/',
      icon: Home,
      isActive: true,
    },
    {
      title: 'Personal Feed',
      url: '/feed',
      icon: Users,
    },
    {
      title: 'Create Post',
      url: '/posts/create',
      icon: Plus,
    },
    // {
    //   title: 'Explore',
    //   url: '/explore',
    //   icon: Code2,
    //   items: [
    //     {
    //       title: 'Recent Posts',
    //       url: '/explore/recent',
    //     },
    //     {
    //       title: 'By Language',
    //       url: '/explore/languages',
    //     },
    //     {
    //       title: 'Popular',
    //       url: '/explore/popular',
    //     },
    //   ],
    // },
  ],
}
</script>

<template>
  <Sidebar v-bind="props">
    <SidebarHeader>
      <TeamSwitcher />
    </SidebarHeader>
    <SidebarContent>
      <SearchBar />
      <NavMain :items="data.navMain" />
    </SidebarContent>
    <SidebarFooter>
      <NavUser />
    </SidebarFooter>
    <SidebarRail />
  </Sidebar>
</template>
