import { defineStore } from 'pinia'
import client from '../api/client'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    loading: true
  }),
  actions: {
    async verifySession() {
      const token = localStorage.getItem('auth_token')
      if (!token) {
        this.user = null
        this.loading = false
        return false
      }
      try {
        const { data } = await client.get('/auth/verify')
        this.user = data.user
        this.loading = false
        return true
      } catch (err) {
        localStorage.removeItem('auth_token')
        this.user = null
        this.loading = false
        return false
      }
    },
    setToken(token) {
      localStorage.setItem('auth_token', token)
    },
    logout() {
      client.post('/auth/logout')
      localStorage.removeItem('auth_token')
      this.user = null
      window.location.href = '/login'
    }
  }
})
