<script setup lang="ts">
import { ref } from 'vue'
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

const mobileMenuOpen = ref(false)

function toggleMobileMenu() {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

function closeMobileMenu() {
  mobileMenuOpen.value = false
}

function handleLogin() {
  // TODO: Implement login functionality
  console.log('Login clicked')
}

function handleToggleTheme() {
  // TODO: Implement theme toggle
  console.log('Theme toggle clicked')
}

function handleOpenNotifications() {
  // TODO: Implement notifications
  console.log('Notifications clicked')
}

function handleOpenProfile() {
  // TODO: Implement profile menu
  console.log('Profile clicked')
}
</script>

<template>
  <header class="sticky top-0 z-50 w-full border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
    <div class="w-full max-w-none flex h-14 items-center px-4">
      <!-- Mobile menu button -->
      <button
        @click="toggleMobileMenu"
        class="inline-flex items-center justify-center rounded-md p-2 text-muted-foreground hover:bg-accent hover:text-accent-foreground focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary md:hidden transition-all duration-200 hover:scale-105"
        aria-controls="mobile-menu"
        :aria-expanded="mobileMenuOpen"
      >
        <span class="sr-only">Open main menu</span>
        <div class="relative w-6 h-6">
          <!-- Hamburger icon -->
          <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 rotate-90 scale-75"
            enter-to-class="opacity-100 rotate-0 scale-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 rotate-0 scale-100"
            leave-to-class="opacity-0 rotate-90 scale-75"
          >
            <svg 
              v-if="!mobileMenuOpen"
              class="absolute inset-0 h-6 w-6" 
              fill="none" 
              viewBox="0 0 24 24" 
              stroke-width="1.5" 
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
          </Transition>
          <!-- Close icon -->
          <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 rotate-90 scale-75"
            enter-to-class="opacity-100 rotate-0 scale-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 rotate-0 scale-100"
            leave-to-class="opacity-0 rotate-90 scale-75"
          >
            <svg 
              v-if="mobileMenuOpen"
              class="absolute inset-0 h-6 w-6" 
              fill="none" 
              viewBox="0 0 24 24" 
              stroke-width="1.5" 
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </Transition>
        </div>
      </button>

      <!-- Logo -->
      <div class="flex items-center ml-2 md:ml-6">
        <Logo />
      </div>

      <!-- Desktop Navigation -->
      <NavigationMenu class="hidden md:flex mx-6">
        <NavigationMenuList>
          <NavigationMenuItem>
            <NavigationMenuTrigger class="h-9 px-4 py-2 transition-all duration-200 hover:scale-105">
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
                class="group inline-flex h-9 w-max items-center justify-center rounded-md bg-background px-4 py-2 text-sm font-medium transition-all duration-200 hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground focus:outline-none disabled:pointer-events-none disabled:opacity-50 data-[active]:bg-accent/50 data-[state=open]:bg-accent/50 hover:scale-105"
              >
                Create
              </router-link>
            </NavigationMenuLink>
          </NavigationMenuItem>
        </NavigationMenuList>
      </NavigationMenu>

      <!-- Right side items -->
      <div class="flex flex-1 items-center justify-end space-x-2">
        <!-- Search - hidden on mobile, shown on tablet+ -->
        <div class="hidden sm:block">
          <SearchBar />
        </div>
        
        <!-- User Actions - always visible but adjusted spacing -->
        <div class="mr-2 md:mr-6">
          <UserActions 
            @login="handleLogin"
            @toggle-theme="handleToggleTheme"
            @open-notifications="handleOpenNotifications"
            @open-profile="handleOpenProfile"
          />
        </div>
      </div>
    </div>

    <!-- Mobile Menu Panel with Animation -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 transform -translate-y-2"
      enter-to-class="opacity-100 transform translate-y-0"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100 transform translate-y-0"
      leave-to-class="opacity-0 transform -translate-y-2"
    >
      <div
        v-if="mobileMenuOpen"
        class="md:hidden overflow-hidden"
        id="mobile-menu"
      >
        <div class="border-t border-border/40 bg-background/95 backdrop-blur px-2 pt-2 pb-3 space-y-1">
          <!-- Mobile Search with stagger animation -->
          <div class="px-3 pb-3 animate-in slide-in-from-top-2 duration-300 delay-75">
            <SearchBar />
          </div>
          
          <!-- Mobile Navigation Links with staggered animation -->
          <router-link
            to="/"
            @click="closeMobileMenu"
            class="block px-3 py-2 rounded-md text-base font-medium text-foreground hover:bg-accent hover:text-accent-foreground transition-all duration-200 animate-in slide-in-from-left-2 duration-300 delay-100"
          >
            Home
          </router-link>
          <router-link
            to="/trending"
            @click="closeMobileMenu"
            class="block px-3 py-2 rounded-md text-base font-medium text-foreground hover:bg-accent hover:text-accent-foreground transition-all duration-200 animate-in slide-in-from-left-2 duration-300 delay-150"
          >
            Trending
          </router-link>
          <router-link
            to="/following"
            @click="closeMobileMenu"
            class="block px-3 py-2 rounded-md text-base font-medium text-foreground hover:bg-accent hover:text-accent-foreground transition-all duration-200 animate-in slide-in-from-left-2 duration-300 delay-200"
          >
            Following
          </router-link>
          <router-link
            to="/create"
            @click="closeMobileMenu"
            class="block px-3 py-2 rounded-md text-base font-medium text-primary hover:bg-accent hover:text-accent-foreground border border-primary/20 transition-all duration-200 hover:scale-105 animate-in slide-in-from-left-2 duration-300 delay-250"
          >
            Create Post
          </router-link>
          <router-link
            to="/profile"
            @click="closeMobileMenu"
            class="block px-3 py-2 rounded-md text-base font-medium text-foreground hover:bg-accent hover:text-accent-foreground transition-all duration-200 animate-in slide-in-from-left-2 duration-300 delay-300"
          >
            Profile
          </router-link>
        </div>
      </div>
    </Transition>
  </header>
</template>