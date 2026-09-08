<template>
  <div class="login-container">
    <div class="glow-orb orb-1"></div>
    <div class="glow-orb orb-2"></div>

    <!-- Theme Toggle at top right -->
    <button 
      @click="toggleTheme" 
      class="login-theme-btn" 
      :title="currentTheme === 'light' ? 'Tünd rejimə keç' : 'Açıq rejimə keç'"
      aria-label="Rejim dəyiş"
    >
      <svg v-if="currentTheme === 'dark'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="5"/>
        <line x1="12" y1="1" x2="12" y2="3"/>
        <line x1="12" y1="21" x2="12" y2="23"/>
        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
        <line x1="1" y1="12" x2="3" y2="12"/>
        <line x1="21" y1="12" x2="23" y2="12"/>
        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
      </svg>
      <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
      </svg>
    </button>

    <div class="login-card">
      <div class="brand-header">
        <div class="brand-badge-icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2a4 4 0 0 1 4 4v2a4 4 0 0 1-8 0V6a4 4 0 0 1 4-4z"/>
            <rect x="3" y="10" width="18" height="12" rx="4"/>
            <circle cx="8" cy="15" r="1.5" fill="currentColor"/>
            <circle cx="16" cy="15" r="1.5" fill="currentColor"/>
            <path d="M10 19h4"/>
          </svg>
        </div>
        <h1 class="brand-heading">AI Writer</h1>
        <div class="system-tag">Alt404 Prime Core</div>
      </div>

      <div class="login-intro">
        <h2>Sistemə Giriş</h2>
        <p>İdarəetmə panelinə daxil olmaq üçün istifadəçi adı və şifrənizi qeyd edin.</p>
      </div>

      <transition name="fade">
        <div v-if="authStore.error" class="login-alert">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          <span>{{ authStore.error }}</span>
        </div>
      </transition>

      <form @submit.prevent="handleSubmit" class="login-form">
        <div class="input-field-group">
          <label for="username">İstifadəçi adı</label>
          <div class="input-wrapper">
            <span class="input-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
            </span>
            <input
              id="username"
              v-model="username"
              type="text"
              placeholder="İstifadəçi adınızı daxil edin"
              required
              autofocus
              autocomplete="username"
              :disabled="authStore.loading"
            />
          </div>
        </div>

        <div class="input-field-group">
          <label for="password">Şifrə</label>
          <div class="input-wrapper">
            <span class="input-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
            </span>
            <input
              id="password"
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              required
              autocomplete="current-password"
              :disabled="authStore.loading"
            />
            <button
              type="button"
              class="pwd-toggle"
              @click="showPassword = !showPassword"
              tabindex="-1"
              :title="showPassword ? 'Gizlət' : 'Göstər'"
            >
              <svg v-if="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
              <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </button>
          </div>
        </div>

        <button type="submit" class="login-submit-btn" :disabled="authStore.loading">
          <svg v-if="authStore.loading" class="spin-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
          </svg>
          <span v-if="authStore.loading">Yoxlanılır...</span>
          <span v-else>Daxil Ol</span>
        </button>
      </form>

      <div class="login-footer">
        <span class="security-note">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          İstifadəçi hesabı yalnız inzibatçı tərəfindən yaradılır
        </span>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'

export default {
  name: 'LoginView',
  setup() {
    const username = ref('')
    const password = ref('')
    const showPassword = ref(false)
    const router = useRouter()
    const route = useRoute()
    const authStore = useAuthStore()

    const currentTheme = ref(localStorage.getItem('ai-writer-theme') || 'dark')
    const toggleTheme = () => {
      currentTheme.value = currentTheme.value === 'light' ? 'dark' : 'light'
      localStorage.setItem('ai-writer-theme', currentTheme.value)
      document.documentElement.classList.remove('theme-dark', 'theme-light')
      document.documentElement.classList.add(`theme-${currentTheme.value}`)
      document.documentElement.setAttribute('data-theme', currentTheme.value)
    }

    const handleSubmit = async () => {
      if (!username.value || !password.value) return

      const ok = await authStore.login(username.value, password.value)
      if (ok) {
        const redirect = route.query.redirect || '/'
        router.push(redirect)
      }
    }

    return {
      username,
      password,
      showPassword,
      authStore,
      currentTheme,
      toggleTheme,
      handleSubmit
    }
  }
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--bg-dark);
  position: relative;
  overflow: hidden;
  padding: 24px;
  transition: background-color 0.25s ease;
}

.login-theme-btn {
  position: absolute;
  top: 24px;
  right: 24px;
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 30;
  box-shadow: var(--shadow-sm);
  transition: all 0.2s ease;
}

.login-theme-btn:hover {
  transform: translateY(-2px);
  border-color: var(--border-glow);
}

.glow-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(120px);
  pointer-events: none;
  opacity: 0.25;
}

.orb-1 {
  width: 450px;
  height: 450px;
  background: radial-gradient(circle, var(--color-accent) 0%, transparent 70%);
  top: -100px;
  left: -100px;
}

.orb-2 {
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, var(--color-primary) 0%, transparent 70%);
  bottom: -80px;
  right: -80px;
}

.login-card {
  width: 100%;
  max-width: 420px;
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-lg);
  padding: 40px 32px;
  position: relative;
  z-index: 10;
  box-shadow: var(--shadow-card);
}

.brand-header {
  text-align: center;
  margin-bottom: 24px;
}

.brand-badge-icon {
  width: 54px;
  height: 54px;
  background: linear-gradient(135deg, rgba(252, 219, 86, 0.15) 0%, rgba(0, 240, 255, 0.15) 100%);
  border: 1px solid rgba(0, 240, 255, 0.3);
  color: var(--color-accent);
  border-radius: var(--radius-md);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 12px;
  box-shadow: 0 0 20px rgba(0, 240, 255, 0.2);
}

.brand-heading {
  font-size: 24px;
  font-weight: 800;
  color: var(--text-main);
  letter-spacing: -0.02em;
}

.system-tag {
  display: inline-block;
  margin-top: 4px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--color-primary);
  background: rgba(252, 219, 86, 0.08);
  padding: 2px 8px;
  border-radius: 4px;
  border: 1px solid rgba(252, 219, 86, 0.2);
}

.login-intro {
  margin-bottom: 24px;
  text-align: center;
}

.login-intro h2 {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-main);
  margin-bottom: 6px;
}

.login-intro p {
  font-size: 13px;
  color: var(--text-muted);
  line-height: 1.4;
}

.login-alert {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.35);
  border-radius: var(--radius-sm);
  color: #fca5a5;
  font-size: 13px;
  margin-bottom: 20px;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.input-field-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  text-align: left;
}

.input-field-group label {
  font-size: 13px;
  font-weight: 500;
  color: var(--text-muted);
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 14px;
  color: var(--text-dim);
  display: flex;
  align-items: center;
  pointer-events: none;
}

.input-wrapper input {
  width: 100%;
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-sm);
  padding: 12px 42px 12px 42px;
  font-size: 14px;
  color: var(--text-main);
  font-family: inherit;
  transition: all 0.2s ease;
  outline: none;
}

.input-wrapper input:focus {
  border-color: var(--color-accent);
  background: rgba(0, 240, 255, 0.04);
  box-shadow: 0 0 0 3px rgba(0, 240, 255, 0.15);
}

.input-wrapper input::placeholder {
  color: var(--text-dim);
}

.pwd-toggle {
  position: absolute;
  right: 12px;
  background: transparent;
  border: none;
  color: var(--text-dim);
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 4px;
  transition: color 0.2s;
}

.pwd-toggle:hover {
  color: var(--text-main);
}

.login-submit-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  background: var(--color-primary);
  color: #07090e;
  font-weight: 700;
  font-size: 14px;
  padding: 13px;
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: all 0.2s ease;
  margin-top: 6px;
  box-shadow: 0 4px 14px rgba(252, 219, 86, 0.25);
}

.login-submit-btn:hover:not(:disabled) {
  background: var(--color-primary-hover);
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(252, 219, 86, 0.35);
}

.login-submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.spin-icon {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.login-footer {
  margin-top: 28px;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  text-align: center;
}

.security-note {
  font-size: 12px;
  color: var(--text-dim);
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
