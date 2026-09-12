<template>
  <div class="app-wrapper">
    <!-- Top Navigation Header (only when logged in) -->
    <header v-if="authStore.isAuthenticated" class="app-header">
      <div class="header-inner">
        <div class="brand-group">
          <div class="brand-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2a4 4 0 0 1 4 4v2a4 4 0 0 1-8 0V6a4 4 0 0 1 4-4z"/>
              <rect x="3" y="10" width="18" height="12" rx="4"/>
              <circle cx="8" cy="15" r="1.5" fill="currentColor"/>
              <circle cx="16" cy="15" r="1.5" fill="currentColor"/>
              <path d="M10 19h4"/>
            </svg>
          </div>
          <div class="brand-text">
            <span class="brand-title">AI Writer</span>
            <span class="brand-sub">Alt404 Prime Intelligence</span>
          </div>
        </div>

        <nav class="nav-links">
          <router-link to="/" class="nav-item" active-class="active">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="7" height="7"/>
              <rect x="14" y="3" width="7" height="7"/>
              <rect x="14" y="14" width="7" height="7"/>
              <rect x="3" y="14" width="7" height="7"/>
            </svg>
            <span>İdarə Paneli</span>
          </router-link>

          <router-link to="/sources" class="nav-item" active-class="active">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="2" y1="12" x2="22" y2="12"/>
              <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
            </svg>
            <span>Mənbələr (12)</span>
          </router-link>

          <router-link to="/history" class="nav-item" active-class="active">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 14 14"/>
            </svg>
            <span>Toplanış Tarixçəsi</span>
          </router-link>

          <router-link to="/news" class="nav-item" active-class="active">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/>
              <path d="M18 14h-8"/>
              <path d="M15 18h-5"/>
              <path d="M10 6h8v4h-8V6Z"/>
            </svg>
            <span>Xəbər Lenti</span>
            <span v-if="store.stats.new > 0" class="badge-new">{{ store.stats.new }}</span>
          </router-link>

          <router-link to="/settings" class="nav-item" active-class="active">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="3"/>
              <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
            <span>Tənzimləmələr</span>
          </router-link>

          <router-link to="/users" class="nav-item" active-class="active">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
              <circle cx="9" cy="7" r="4"></circle>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <span>İstifadəçilər</span>
          </router-link>
        </nav>

        <div class="header-actions">
          <!-- Theme Toggle Button -->
          <button 
            @click="toggleTheme" 
            class="btn-theme-toggle" 
            :title="currentTheme === 'light' ? 'Tünd rejimə keç' : 'Açıq rejimə keç'"
            aria-label="Rejim dəyiş"
          >
            <svg v-if="currentTheme === 'dark'" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
            <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
            <span class="theme-text">{{ currentTheme === 'light' ? 'Tünd' : 'Açıq' }}</span>
          </button>

          <button @click="store.grabAllSources()" :disabled="store.loading" class="btn btn-secondary btn-sm" title="Bütün mənbələrdən xəbərləri topla">
            <svg class="spin-on-load" :class="{ spinning: store.loading }" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
            </svg>
            <span>Mənbələri Yoxla</span>
          </button>

          <button @click="store.batchProcess()" :disabled="store.loading || store.stats.new === 0" class="btn btn-primary btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
            </svg>
            <span>Qaralama Hazırla</span>
          </button>

          <!-- User profile & Logout -->
          <div class="user-session-info" v-if="authStore.user">
            <div class="user-avatar-mini">
              {{ (authStore.user.name || authStore.user.username).charAt(0).toUpperCase() }}
            </div>
            <span class="user-session-name">{{ authStore.user.username }}</span>
          </div>

          <button @click="authStore.logout()" class="btn btn-ghost btn-sm btn-logout" title="Sistemdən Çıxış">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
              <polyline points="16 17 21 12 16 7"></polyline>
              <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span>Çıxış</span>
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="app-main">
      <router-view />
    </main>

    <!-- Toast Notification -->
    <transition name="toast">
      <div v-if="store.toast" class="toast-alert" :class="'toast-' + store.toast.type">
        <div class="toast-icon">
          <svg v-if="store.toast.type === 'success'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M20 6L9 17l-5-5"/>
          </svg>
          <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
        </div>
        <div class="toast-message">{{ store.toast.message }}</div>
      </div>
    </transition>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useAiWriterStore } from './stores/aiWriter'
import { useAuthStore } from './stores/auth'

export default {
  name: 'App',
  setup() {
    const store = useAiWriterStore()
    const authStore = useAuthStore()
    const currentTheme = ref('dark')

    const applyTheme = (theme) => {
      document.documentElement.classList.remove('theme-dark', 'theme-light')
      document.documentElement.classList.add(`theme-${theme}`)
      document.documentElement.setAttribute('data-theme', theme)
    }

    const toggleTheme = () => {
      currentTheme.value = currentTheme.value === 'light' ? 'dark' : 'light'
      localStorage.setItem('ai-writer-theme', currentTheme.value)
      applyTheme(currentTheme.value)
    }

    onMounted(() => {
      const savedTheme = localStorage.getItem('ai-writer-theme') || 'dark'
      currentTheme.value = savedTheme
      applyTheme(savedTheme)

      if (authStore.isAuthenticated) {
        store.fetchStats()
      }
      // Periodic stats sync every 45s if logged in
      setInterval(() => {
        if (authStore.isAuthenticated) {
          store.fetchStats()
        }
      }, 45000)
    })

    return { store, authStore, currentTheme, toggleTheme }
  }
}
</script>

<style>
/* Modern CSS Reset & Theme Tokens */
:root, :root.theme-dark {
  --bg-dark: #07090e;
  --bg-surface: #0e131f;
  --bg-card: #131929;
  --bg-card-hover: #192237;
  --border-subtle: #1f293d;
  --border-glow: rgba(0, 240, 255, 0.25);
  
  --color-primary: #FCDB56;
  --color-primary-hover: #ffe066;
  --color-accent: #00F0FF;
  --color-accent-dim: rgba(0, 240, 255, 0.12);
  
  --text-main: #f3f4f6;
  --text-muted: #94a3b8;
  --text-dim: #64748b;
  
  --status-new: #38bdf8;
  --status-posted: #10b981;
  --status-duplicate: #f59e0b;
  --status-error: #ef4444;
  --status-generating: #a855f7;

  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-full: 9999px;
  
  --font-sans: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;

  --bg-header: rgba(14, 19, 31, 0.85);
  --brand-gradient: linear-gradient(90deg, #FFFFFF, var(--color-primary));
  --nav-bg: rgba(7, 9, 14, 0.6);
  --nav-border: rgba(255, 255, 255, 0.05);
  --nav-hover-bg: rgba(255, 255, 255, 0.04);
  --btn-secondary-bg: rgba(255, 255, 255, 0.06);
  --btn-secondary-hover: rgba(255, 255, 255, 0.1);
  --user-session-bg: rgba(255, 255, 255, 0.04);
  --shadow-dropdown: 0 10px 30px rgba(0, 0, 0, 0.6);
  --shadow-card: 0 4px 20px rgba(0, 0, 0, 0.35);
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.3);
}

:root.theme-light {
  --bg-dark: #f8fafc;
  --bg-surface: #ffffff;
  --bg-card: #ffffff;
  --bg-card-hover: #f1f5f9;
  --border-subtle: #e2e8f0;
  --border-glow: rgba(14, 165, 233, 0.25);
  
  --color-primary: #f59e0b;
  --color-primary-hover: #d97706;
  --color-accent: #0284c7;
  --color-accent-dim: rgba(2, 132, 199, 0.1);
  
  --text-main: #0f172a;
  --text-muted: #475569;
  --text-dim: #94a3b8;
  
  --status-new: #0284c7;
  --status-posted: #059669;
  --status-duplicate: #d97706;
  --status-error: #dc2626;
  --status-generating: #9333ea;

  --bg-header: rgba(255, 255, 255, 0.92);
  --brand-gradient: linear-gradient(90deg, #0f172a, #d97706);
  --nav-bg: #e2e8f0;
  --nav-border: #cbd5e1;
  --nav-hover-bg: rgba(0, 0, 0, 0.05);
  --btn-secondary-bg: #ffffff;
  --btn-secondary-hover: #f1f5f9;
  --user-session-bg: rgba(0, 0, 0, 0.04);
  --shadow-dropdown: 0 10px 25px rgba(0, 0, 0, 0.08);
  --shadow-card: 0 4px 16px rgba(0, 0, 0, 0.06);
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08);
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  background-color: var(--bg-dark);
  color: var(--text-main);
  font-family: var(--font-sans);
  -webkit-font-smoothing: antialiased;
  min-height: 100vh;
  transition: background-color 0.25s ease, color 0.25s ease;
}

.app-wrapper {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Header */
.app-header {
  background: var(--bg-header);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-bottom: 1px solid var(--border-subtle);
  position: sticky;
  top: 0;
  z-index: 50;
  transition: background 0.25s ease, border-color 0.25s ease;
}

.header-inner {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 24px;
  height: 70px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
}

.brand-group {
  display: flex;
  align-items: center;
  gap: 12px;
}

.brand-icon {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-md);
  background: linear-gradient(135deg, rgba(252, 219, 86, 0.15) 0%, rgba(0, 240, 255, 0.15) 100%);
  border: 1px solid rgba(252, 219, 86, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-primary);
}

.brand-title {
  display: block;
  font-size: 1.15rem;
  font-weight: 700;
  letter-spacing: -0.5px;
  background: var(--brand-gradient);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}

.brand-sub {
  display: block;
  font-size: 0.72rem;
  color: var(--text-dim);
  text-transform: uppercase;
  letter-spacing: 0.8px;
}

/* Nav */
.nav-links {
  display: flex;
  align-items: center;
  gap: 6px;
  background: var(--nav-bg);
  padding: 5px;
  border-radius: var(--radius-md);
  border: 1px solid var(--nav-border);
  transition: background 0.25s ease, border-color 0.25s ease;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  font-size: 0.88rem;
  font-weight: 500;
  color: var(--text-muted);
  text-decoration: none;
  border-radius: var(--radius-sm);
  transition: all 0.2s ease;
  position: relative;
}

.nav-item:hover {
  color: var(--text-main);
  background: var(--nav-hover-bg);
}

.nav-item.active {
  color: #07090e;
  background: var(--color-primary);
  font-weight: 600;
}

.badge-new {
  background: var(--status-new);
  color: #07090e;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: var(--radius-full);
}

.nav-item.active .badge-new {
  background: #07090e;
  color: var(--color-primary);
}

/* Header buttons */
.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

/* Theme Toggle Button */
.btn-theme-toggle {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 12px;
  font-size: 0.82rem;
  font-weight: 600;
  font-family: var(--font-sans);
  border-radius: var(--radius-md);
  background: var(--btn-secondary-bg);
  border: 1px solid var(--border-subtle);
  color: var(--text-main);
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-theme-toggle:hover {
  background: var(--btn-secondary-hover);
  border-color: var(--border-glow);
  transform: translateY(-1px);
}

.btn-theme-toggle svg {
  color: var(--color-primary);
  flex-shrink: 0;
}

.theme-text {
  font-size: 0.78rem;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-family: var(--font-sans);
  font-size: 0.88rem;
  font-weight: 600;
  padding: 10px 18px;
  border-radius: var(--radius-md);
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
}

.btn-sm {
  padding: 7px 13px;
  font-size: 0.8rem;
}

.btn-primary {
  background: var(--color-primary);
  color: #07090e;
}
.btn-primary:hover:not(:disabled) {
  background: var(--color-primary-hover);
  transform: translateY(-1px);
  box-shadow: 0 4px 15px rgba(245, 158, 11, 0.25);
}

.btn-secondary {
  background: var(--btn-secondary-bg);
  border: 1px solid var(--border-subtle);
  color: var(--text-main);
}
.btn-secondary:hover:not(:disabled) {
  background: var(--btn-secondary-hover);
  border-color: var(--border-glow);
}

.btn-cyan {
  background: var(--color-accent-dim);
  border: 1px solid rgba(2, 132, 199, 0.3);
  color: var(--color-accent);
}
.btn-cyan:hover:not(:disabled) {
  background: rgba(2, 132, 199, 0.2);
  box-shadow: 0 0 15px rgba(2, 132, 199, 0.25);
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

/* Spin animation */
.spinning {
  animation: spin 1s infinite linear;
}
@keyframes spin {
  100% { transform: rotate(360deg); }
}

/* Main */
.app-main {
  flex: 1;
  max-width: 1400px;
  width: 100%;
  margin: 0 auto;
  padding: 32px 24px;
}

/* Toast */
.toast-alert {
  position: fixed;
  bottom: 30px;
  right: 30px;
  padding: 14px 20px;
  border-radius: var(--radius-md);
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: var(--shadow-dropdown);
  z-index: 100;
  max-width: 420px;
  font-size: 0.88rem;
  color: var(--text-main);
}

.toast-success {
  border-color: rgba(16, 185, 129, 0.4);
  color: #10b981;
}

.toast-error {
  border-color: rgba(239, 68, 68, 0.4);
  color: #ef4444;
}

.toast-enter-active, .toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from, .toast-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

/* User session badge & logout */
.user-session-info {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 12px 4px 6px;
  background: var(--user-session-bg);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-full);
}

.user-avatar-mini {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(252, 219, 86, 0.3) 0%, rgba(0, 240, 255, 0.3) 100%);
  border: 1px solid rgba(0, 240, 255, 0.4);
  color: var(--color-accent);
  font-size: 11px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
}

.user-session-name {
  font-size: 12.5px;
  font-weight: 600;
  color: var(--text-main);
}

.btn-ghost {
  background: transparent;
  color: var(--text-muted);
}
.btn-ghost:hover:not(:disabled) {
  background: rgba(125, 125, 125, 0.1);
  color: var(--text-main);
}

.btn-logout {
  color: #f87171 !important;
  border: 1px solid transparent;
}
.btn-logout:hover:not(:disabled) {
  background: rgba(239, 68, 68, 0.12) !important;
  border-color: rgba(239, 68, 68, 0.3);
  color: #fca5a5 !important;
}
</style>
