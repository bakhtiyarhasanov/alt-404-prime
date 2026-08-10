<template>
  <div class="login-page">
    <div class="login-card">
      <div class="header">
        <span class="logo">alt404</span>
        <span class="badge">MANAGER</span>
      </div>
      <p class="subtitle">İdarəetmə panelinə daxil olun</p>
      
      <div v-if="error" class="error-box">{{ error }}</div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label for="email">E-poçt</label>
          <input 
            type="email" 
            id="email" 
            v-model="email" 
            placeholder="admin@alt404.com" 
            required 
            :disabled="loading"
            class="form-input"
          />
        </div>
        
        <div class="form-group">
          <label for="password">Şifrə</label>
          <input 
            type="password" 
            id="password" 
            v-model="password" 
            placeholder="••••••••" 
            required 
            :disabled="loading"
            class="form-input"
          />
        </div>

        <button type="submit" class="submit-btn" :disabled="loading">
          <span v-if="loading">Daxil olunur...</span>
          <span v-else>Daxil Ol</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import client from '../api/client'

export default {
  name: 'LoginView',
  setup() {
    const email = ref('')
    const password = ref('')
    const error = ref('')
    const loading = ref(false)
    const router = useRouter()
    const authStore = useAuthStore()

    const handleLogin = async () => {
      loading.value = true
      error.value = ''
      try {
        const { data } = await client.post('/auth/login', {
          email: email.value,
          password: password.value
        })
        if (data.token) {
          authStore.setToken(data.token)
          await authStore.verifySession()
          router.push('/')
        } else {
          error.value = 'Xətalı cavab alındı'
        }
      } catch (err) {
        error.value = err.response?.data?.error || 'Sistem xətası baş verdi'
      } finally {
        loading.value = false
      }
    }

    return {
      email,
      password,
      handleLogin,
      loading,
      error
    }
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--color-bg);
}
.login-card {
  width: 100%;
  max-width: 380px;
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 16px;
  padding: 40px 32px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.24);
}
.header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-bottom: 8px;
}
.logo {
  font-family: 'Tomorrow', sans-serif;
  font-weight: 700;
  font-size: 24px;
  color: #fff;
}
.badge {
  background: var(--color-primary);
  color: #111;
  font-size: 9px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 4px;
  letter-spacing: 0.05em;
}
.subtitle {
  color: var(--color-text-secondary);
  font-size: 14px;
  margin: 0 0 28px;
  text-align: center;
}
.login-form {
  text-align: left;
}
.form-group {
  margin-bottom: 20px;
}
.form-group label {
  display: block;
  font-size: 13px;
  font-weight: 500;
  color: var(--color-text-secondary);
  margin-bottom: 6px;
}
.form-input {
  width: 100%;
  background-color: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 10px 14px;
  color: #fff;
  font-size: 14px;
  transition: all 0.2s ease;
  box-sizing: border-box;
}
.form-input:focus {
  outline: none;
  border-color: var(--color-primary);
  background-color: rgba(255, 255, 255, 0.06);
  box-shadow: 0 0 0 2px rgba(0, 229, 255, 0.15);
}
.submit-btn {
  width: 100%;
  background: var(--color-primary);
  color: #111;
  font-weight: 600;
  padding: 12px;
  border-radius: 8px;
  border: none;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
  margin-top: 10px;
}
.submit-btn:hover:not(:disabled) {
  opacity: 0.9;
  transform: translateY(-1px);
}
.submit-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.error-box {
  background-color: rgba(239, 68, 68, 0.1);
  border: 1px solid var(--color-red);
  color: #fca5a5;
  padding: 10px;
  border-radius: 8px;
  font-size: 13px;
  margin-bottom: 20px;
  text-align: center;
}
</style>
