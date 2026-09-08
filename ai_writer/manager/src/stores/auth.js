import { defineStore } from 'pinia'
import api from '../api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('ai_writer_token') || null,
    loading: false,
    error: null,
    sessionChecked: false
  }),

  getters: {
    isAuthenticated: (state) => !!state.token
  },

  actions: {
    async login(username, password) {
      this.loading = true
      this.error = null
      try {
        const res = await api.post('?endpoint=auth&action=login', {
          username: username.trim(),
          password
        })

        if (res.data?.success && res.data?.token) {
          this.token = res.data.token
          this.user = res.data.user
          localStorage.setItem('ai_writer_token', res.data.token)
          this.sessionChecked = true
          return true
        } else {
          this.error = res.data?.error || 'Giriş uğursuz oldu'
          return false
        }
      } catch (err) {
        this.error = err.response?.data?.error || err.message || 'Giriş zamanı xəta baş verdi'
        return false
      } finally {
        this.loading = false
      }
    },

    async verifySession() {
      const storedToken = localStorage.getItem('ai_writer_token')
      if (!storedToken) {
        this.user = null
        this.token = null
        this.sessionChecked = true
        return false
      }

      this.token = storedToken
      try {
        const res = await api.get('?endpoint=auth&action=verify')
        if (res.data?.success && res.data?.user) {
          this.user = res.data.user
          this.sessionChecked = true
          return true
        } else {
          this.clearAuth()
          return false
        }
      } catch (err) {
        this.clearAuth()
        return false
      }
    },

    async logout() {
      try {
        await api.post('?endpoint=auth&action=logout')
      } catch (e) {
        // Ignore network errors on logout
      }
      this.clearAuth()
      window.location.href = '/login'
    },

    clearAuth() {
      this.user = null
      this.token = null
      this.sessionChecked = true
      localStorage.removeItem('ai_writer_token')
    }
  }
})
