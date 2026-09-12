import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/Login.vue'),
    meta: { guestOnly: true }
  },
  {
    path: '/login-success',
    name: 'LoginSuccess',
    component: {
      template: '<div>Giriş edilir...</div>',
      mounted() {
        const token = this.$route.query.token
        if (token) {
          const auth = useAuthStore()
          auth.setToken(token)
          this.$router.push('/')
        } else {
          this.$router.push('/login')
        }
      }
    }
  },
  {
    path: '/',
    component: () => import('../components/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: () => import('../views/Dashboard.vue')
      },
      {
        path: 'articles',
        name: 'Articles',
        component: () => import('../views/Articles.vue')
      },
      {
        path: 'articles/new',
        name: 'NewArticle',
        component: () => import('../views/ArticleEditor.vue')
      },
      {
        path: 'articles/edit/:id',
        name: 'EditArticle',
        component: () => import('../views/ArticleEditor.vue')
      },
      {
        path: 'categories',
        name: 'Categories',
        component: () => import('../views/Categories.vue')
      },
      {
        path: 'ads',
        name: 'Ads',
        component: () => import('../views/Ads.vue')
      },
      {
        path: 'media',
        name: 'Media',
        component: () => import('../views/Media.vue')
      },
      {
        path: 'contacts',
        name: 'Contacts',
        component: () => import('../views/Contacts.vue')
      },
      {
        path: 'videos',
        name: 'Videos',
        component: () => import('../views/Videos.vue')
      },
      {
        path: 'projects',
        name: 'Projects',
        component: () => import('../views/Projects.vue')
      },
      {
        path: 'users',
        name: 'Users',
        component: () => import('../views/Users.vue')
      },
      {
        path: 'settings',
        name: 'Settings',
        component: () => import('../views/Settings.vue')
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Authentication guard
router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()
  
  if (auth.loading) {
    await auth.verifySession()
  }

  if (to.meta.requiresAuth && !auth.user) {
    next('/login')
  } else if (to.meta.guestOnly && auth.user) {
    next('/')
  } else {
    next()
  }
})

export default router
