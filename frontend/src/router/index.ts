import { createRouter, createWebHistory } from 'vue-router'
import HomePage from '../pages/HomePage.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomePage,
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../pages/LoginPage.vue'),
      meta: { guest: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../pages/RegisterPage.vue'),
      meta: { guest: true },
    },
    {
      path: '/auth/callback',
      name: 'auth-callback',
      component: () => import('../pages/AuthCallback.vue'),
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('../pages/UserDashboard.vue'),
      meta: { requiresAuth: true, role: 'user' },
    },
    {
      path: '/booking',
      name: 'booking',
      component: () => import('../pages/BookingPage.vue'),
      meta: { requiresAuth: true, role: 'user' },
    },
    {
      path: '/admin',
      name: 'admin',
      component: () => import('../pages/AdminDashboard.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/privacy',
      name: 'privacy',
      component: () => import('../pages/PrivacyPolicyPage.vue'),
    },
    {
      path: '/about/privacy',
      name: 'about-privacy',
      redirect: '/privacy',
    },
  ],
  scrollBehavior(_to, _from, savedPosition) {
    if (savedPosition) return savedPosition
    return { top: 0 }
  },
})

// ─── Navigation Guards ───
router.beforeEach((to, _from, next) => {
  const token = localStorage.getItem('auth_token')
  const userStr = localStorage.getItem('user')
  let user: { role?: string } = {}
  
  try {
    user = userStr ? JSON.parse(userStr) : {}
  } catch {
    user = {}
  }

  // Route requires authentication
  if (to.meta.requiresAuth) {
    if (!token) {
      // Not logged in → redirect to login
      return next({ name: 'login' })
    }

    // Check role-based access
    if (to.meta.role && user.role !== to.meta.role) {
      // Wrong role → redirect to appropriate dashboard
      if (user.role === 'admin') {
        return next({ name: 'admin' })
      } else {
        return next({ name: 'dashboard' })
      }
    }
  }

  // Guest-only routes (login/register) — redirect if already logged in
  if (to.meta.guest && token) {
    if (user.role === 'admin') {
      return next({ name: 'admin' })
    } else {
      return next({ name: 'dashboard' })
    }
  }

  next()
})

export default router
