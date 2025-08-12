import { createRouter, createWebHistory } from 'vue-router'
import HomePage from '@/pages/HomePage.vue'
import { useUserStore } from '@/stores/UserStore'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomePage,
    meta: { layout: 'app', requiresAuth: false }
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/LoginPage.vue'),
    meta: { layout: 'empty', requiresAuth: false }
  },
  {
    path: '/signup',
    name: 'signup',
    component: () => import('@/pages/SignUpPage.vue'),
    meta: { layout: 'empty', requiresAuth: false }
  },
  {
    path: '/profile/edit',
    name: 'profile-edit',
    component: () => import('@/pages/ProfileEditPage.vue'),
    meta: { layout: 'app', requiresAuth: true }
  }
  // {
  //   path: '/trending',
  //   name: 'trending',
  //   component: () => import('@/pages/TrendingPage.vue'),
  //   meta: { requiresAuth: true }
  // },
  // {
  //   path: '/following',
  //   name: 'following',
  //   component: () => import('@/pages/FollowingPage.vue'),
  //   meta: { requiresAuth: true }
  // },
  // {
  //   path: '/create',
  //   name: 'create',
  //   component: () => import('@/pages/CreatePage.vue'),
  //   meta: { requiresAuth: true }
  // },
  // {
  //   path: '/profile',
  //   name: 'profile',
  //   component: () => import('@/pages/ProfilePage.vue'),
  //   meta: { requiresAuth: true }
  // },
  // {
  //   path: '/:pathMatch(.*)*',
  //   name: 'not-found',
  //   component: () => import('@/pages/NotFoundPage.vue')
  // }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Route guards for authentication
router.beforeEach((to, _, next) => {
  const userStore = useUserStore()
  
  // Check if route requires authentication
  const requiresAuth = to.meta.requiresAuth === true
  
  // If route requires auth and user is not authenticated
  if (requiresAuth && !userStore.getIsAuthenticated) {
    // Redirect to login page
    next('/login')
    return
  }
  
  // If user is authenticated and trying to access login/signup pages
  if (userStore.getIsAuthenticated && (to.name === 'login' || to.name === 'signup')) {
    // Redirect to home page
    next('/')
    return
  }
  
  // Allow navigation
  next()
})

export default router