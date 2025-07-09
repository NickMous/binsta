import { createRouter, createWebHistory } from 'vue-router'
import HomePage from '@/pages/HomePage.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomePage
  },
  // {
  //   path: '/trending',
  //   name: 'trending',
  //   component: () => import('@/pages/TrendingPage.vue')
  // },
  // {
  //   path: '/following',
  //   name: 'following',
  //   component: () => import('@/pages/FollowingPage.vue')
  // },
  // {
  //   path: '/create',
  //   name: 'create',
  //   component: () => import('@/pages/CreatePage.vue')
  // },
  // {
  //   path: '/profile',
  //   name: 'profile',
  //   component: () => import('@/pages/ProfilePage.vue')
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

export default router