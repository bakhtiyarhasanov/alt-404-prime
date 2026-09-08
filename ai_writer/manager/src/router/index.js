import { createRouter, createWebHistory } from 'vue-router'
import Dashboard from '../views/Dashboard.vue'
import Sources from '../views/Sources.vue'
import NewsFeed from '../views/NewsFeed.vue'
import Settings from '../views/Settings.vue'

const routes = [
  {
    path: '/',
    name: 'Dashboard',
    component: Dashboard
  },
  {
    path: '/sources',
    name: 'Sources',
    component: Sources
  },
  {
    path: '/news',
    name: 'NewsFeed',
    component: NewsFeed
  },
  {
    path: '/settings',
    name: 'Settings',
    component: Settings
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

export default router
