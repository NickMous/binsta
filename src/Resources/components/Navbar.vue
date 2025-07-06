<script setup lang="ts">
import {
  NavigationMenu,
  NavigationMenuContent,
  NavigationMenuItem,
  NavigationMenuLink,
  NavigationMenuList,
  NavigationMenuTrigger,
} from '@/components/ui/navigation-menu'
import Logo from './Logo.vue'
import SearchBar from './SearchBar.vue'
import UserActions from './UserActions.vue'
</script>

<template>
  <header class="sticky top-0 z-50 w-full border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
    <div class="container flex h-14 max-w-screen-2xl items-center">
      <NavigationMenu class="mx-6">
        <NavigationMenuList>
          <NavigationMenuItem>
            <Logo />
          </NavigationMenuItem>
          <NavigationMenuItem>
            <NavigationMenuTrigger class="h-9 px-4 py-2">
              Explore
            </NavigationMenuTrigger>
            <NavigationMenuContent>
              <div class="grid gap-3 p-6 md:w-[400px] lg:w-[500px] lg:grid-cols-[.75fr_1fr]">
                <div class="row-span-3">
                  <NavigationMenuLink as-child>
                    <router-link
                      to="/"
                      class="flex h-full w-full select-none flex-col justify-end rounded-md bg-gradient-to-b from-muted/50 to-muted p-6 no-underline outline-none focus:shadow-md"
                    >
                      <div class="mb-2 mt-4 text-lg font-medium">
                        Discover
                      </div>
                      <p class="text-sm leading-tight text-muted-foreground">
                        Explore trending posts and connect with the community
                      </p>
                    </router-link>
                  </NavigationMenuLink>
                </div>
                <div class="grid gap-1">
                  <NavigationMenuLink as-child>
                    <router-link 
                      to="/trending"
                      class="block select-none space-y-1 rounded-md p-3 leading-none no-underline outline-none transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground"
                    >
                      <div class="text-sm font-medium leading-none">Trending</div>
                      <p class="line-clamp-2 text-sm leading-snug text-muted-foreground">
                        See what's popular right now
                      </p>
                    </router-link>
                  </NavigationMenuLink>
                  <NavigationMenuLink as-child>
                    <router-link 
                      to="/following"
                      class="block select-none space-y-1 rounded-md p-3 leading-none no-underline outline-none transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground"
                    >
                      <div class="text-sm font-medium leading-none">Following</div>
                      <p class="line-clamp-2 text-sm leading-snug text-muted-foreground">
                        Posts from people you follow
                      </p>
                    </router-link>
                  </NavigationMenuLink>
                </div>
              </div>
            </NavigationMenuContent>
          </NavigationMenuItem>
          <NavigationMenuItem>
            <NavigationMenuLink as-child>
              <router-link 
                to="/create"
                class="group inline-flex h-9 w-max items-center justify-center rounded-md bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground focus:outline-none disabled:pointer-events-none disabled:opacity-50 data-[active]:bg-accent/50 data-[state=open]:bg-accent/50"
              >
                Create
              </router-link>
            </NavigationMenuLink>
          </NavigationMenuItem>
        </NavigationMenuList>
      </NavigationMenu>

      <div class="flex flex-1 items-center justify-between space-x-2 md:justify-end">
        <SearchBar />
        <div class="mr-6">
          <UserActions />
        </div>
      </div>
    </div>
  </header>
</template>