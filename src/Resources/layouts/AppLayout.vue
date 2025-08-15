<script lang="ts">
</script>

<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue'
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb'
import {Separator} from '@/components/ui/separator'
import {
  SidebarInset,
  SidebarProvider,
  SidebarTrigger,
} from '@/components/ui/sidebar'
import {useBreadcrumbStore} from '@/stores/BreadcrumbStore.ts'

const breadcrumbStore = useBreadcrumbStore()
</script>

<template>
  <SidebarProvider>
    <AppSidebar/>
    <SidebarInset>
      <header
          class="flex h-16 shrink-0 items-center gap-2 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12">
        <div class="flex items-center gap-2 px-4">
          <SidebarTrigger class="-ml-1"/>
          <Separator orientation="vertical" class="mr-2 h-4"/>
          <Breadcrumb>
            <BreadcrumbList>
              <template v-for="(breadcrumb, index) in breadcrumbStore.getBreadcrumbs" :key="breadcrumb.path">
                <BreadcrumbItem class="hidden md:block">
                  <BreadcrumbLink v-if="index < breadcrumbStore.getBreadcrumbs.length - 1" :href="breadcrumb.path">
                    {{ breadcrumb.name }}
                  </BreadcrumbLink>
                  <BreadcrumbPage v-else>
                    {{ breadcrumb.name }}
                  </BreadcrumbPage>
                </BreadcrumbItem>
                <BreadcrumbSeparator v-if="index < breadcrumbStore.getBreadcrumbs.length - 1" class="hidden md:block"/>
              </template>
            </BreadcrumbList>
          </Breadcrumb>
        </div>
      </header>
      <div class="flex flex-1 flex-col gap-4 p-4 pt-0">
        <router-view v-slot="{ Component }">
          <transition name="page" mode="out-in">
            <component :is="Component"/>
          </transition>
        </router-view>
      </div>
    </SidebarInset>
  </SidebarProvider>
</template>
