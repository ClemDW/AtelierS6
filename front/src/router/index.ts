import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: { requiresAuth: true }
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue')
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue')
    },
    {
      path: '/galeries',
      name: 'galeries',
      component: () => import('../views/GaleriesView.vue')
    },
    {
      path: '/galeries/:id',
      name: 'galerie-detail',
      component: () => import('../views/GalerieDetailView.vue')
    }
  ],
})

router.beforeEach((to, from) => {
  const authStore = useAuthStore()

  // If route requires authentication and user is not authenticated -> redirect to /login
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return { name: 'login' }
  }

  // If user is authenticated and tries to access login or register -> redirect to /home
  if ((to.name === 'login' || to.name === 'register') && authStore.isAuthenticated) {
    return { name: 'home' }
  }
})

export default router
