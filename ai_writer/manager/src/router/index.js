import { createRouter, createWebHistory } from 'vue-router'
import Dashboard from '../views/Dashboard.vue'
import Sources from '../views/Sources.vue'
import NewsFeed from '../views/NewsFeed.vue'
import Settings from '../views/Settings.vue'
import Users from '../views/Users.vue'
import Login from '../views/Login.vue'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { guestOnly: true }
  },
  {
    path: '/',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/sources',
    name: 'Sources',
    component: Sources,
    meta: { requiresAuth: true }
  },
  {
    path: '/history',
    name: 'GrabHistory',
    component: () => import('../views/GrabHistory.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/news',
    name: 'NewsFeed',
    component: NewsFeed,
    meta: { requiresAuth: true }
  },
  {
    path: '/settings',
    name: 'Settings',
    component: Settings,
    meta: { requiresAuth: true }
  },
  {
    path: '/users',
    name: 'Users',
    component: Users,
    meta: { requiresAuth: true }
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  if (!authStore.sessionChecked) {
    await authStore.verifySession()
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ path: '/login', query: { redirect: to.fullPath !== '/login' ? to.fullPath : '/' } })
  }

  if (to.meta.guestOnly && authStore.isAuthenticated) {
    return next({ path: '/' })
  }

  next()
})

export default router
