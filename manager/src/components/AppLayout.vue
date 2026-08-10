<template>
  <div class="layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="logo">
        <span class="logo-text">alt404</span>
        <span class="badge">MANAGER</span>
      </div>

      <nav class="nav">
        <router-link to="/" class="nav-item" exact-active-class="active">
          <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
          Panel
        </router-link>
        <router-link to="/articles" class="nav-item" active-class="active">
          <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          Məqalələr
        </router-link>
        <router-link to="/categories" class="nav-item" active-class="active">
          <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/></svg>
          Kateqoriyalar
        </router-link>
        <router-link to="/ads" class="nav-item" active-class="active">
          <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
          Reklamlar
        </router-link>
        <router-link to="/media" class="nav-item" active-class="active">
          <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M20.42 17.58L15 12l-4 4-2-2-4.42 4.42"/></svg>
          Media Kitabxanası
        </router-link>
        <router-link to="/videos" class="nav-item" active-class="active">
          <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
          Videolar
        </router-link>
        <router-link to="/contacts" class="nav-item" active-class="active">
          <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          Müraciətlər
        </router-link>
        <router-link v-if="user && user.role === 'editor'" to="/users" class="nav-item" active-class="active">
          <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          İstifadəçilər
        </router-link>
        <router-link to="/settings" class="nav-item" active-class="active">
          <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
          Tənzimləmələr
        </router-link>
      </nav>

      <div class="user-section" v-if="user">
        <img :src="user.avatar_url || 'https://images.pexels.com/photos/531880/pexels-photo-531880.jpeg'" class="avatar" alt="">
        <div class="user-info">
          <span class="user-name">{{ user.name }}</span>
          <button @click="logout" class="logout-btn">Çıxış</button>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main">
      <header class="topbar">
        <h1 class="page-title">{{ routeName }}</h1>
        <div class="theme-selector">
          <svg class="theme-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
          <select v-model="currentTheme" @change="changeTheme" class="theme-select">
            <option value="midnight">Midnight (Tünd)</option>
            <option value="light">Açıq (Light)</option>
            <option value="cyberpunk">Cyberpunk (Neon)</option>
            <option value="forest">Forest (Yaşıl)</option>
            <option value="sunset">Sunset (Çəhrayı)</option>
          </select>
        </div>
      </header>
      <div class="content">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'

export default {
  name: 'AppLayout',
  setup() {
    const route = useRoute()
    const auth = useAuthStore()
    const currentTheme = ref('midnight')

    onMounted(() => {
      currentTheme.value = localStorage.getItem('admin-theme') || 'midnight'
    })

    const changeTheme = () => {
      localStorage.setItem('admin-theme', currentTheme.value)
      document.documentElement.className = `theme-${currentTheme.value}`
    }

    const routeName = computed(() => {
      const mappings = {
        Dashboard: 'Panel',
        Articles: 'Məqalələr',
        NewArticle: 'Yeni Məqalə',
        EditArticle: 'Məqalə Redaktəsi',
        Categories: 'Kateqoriyalar',
        Ads: 'Reklam Bannerləri',
        Media: 'Media Kitabxanası',
        Contacts: 'Əlaqə Müraciətləri',
        Videos: 'Videolar',
        Users: 'İstifadəçilər',
        Settings: 'Tənzimləmələr'
      }
      return mappings[route.name] || 'Manager'
    })

    return {
      user: computed(() => auth.user),
      logout: auth.logout,
      routeName,
      currentTheme,
      changeTheme
    }
  }
}
</script>

<style scoped>
.layout {
  display: flex;
  min-height: 100vh;
}
.sidebar {
  width: 260px;
  background-color: var(--color-surface);
  border-right: 1px solid var(--color-border);
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  transition: background-color 0.3s ease, border-color 0.3s ease;
}
.logo {
  padding: 24px;
  display: flex;
  align-items: center;
  gap: 8px;
  border-bottom: 1px solid var(--color-border);
}
.logo-text {
  font-family: 'Tomorrow', sans-serif;
  font-weight: 700;
  font-size: 20px;
  color: var(--color-text-primary);
}
.badge {
  background: var(--color-primary);
  color: #111;
  font-size: 8px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 4px;
  letter-spacing: 0.05em;
}
.nav {
  flex: 1;
  padding: 24px 16px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border-radius: 8px;
  color: var(--color-text-secondary);
  text-decoration: none;
  font-weight: 500;
  font-size: 14px;
  transition: all 0.2s;
}
.nav-item:hover, .nav-item.active {
  color: var(--color-text-primary);
  background-color: var(--color-surface-hover);
}
.nav-item.active {
  color: var(--color-primary);
}
.icon {
  width: 18px;
  height: 18px;
}
.user-section {
  padding: 20px;
  border-top: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  gap: 12px;
}
.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
}
.user-info {
  display: flex;
  flex-direction: column;
}
.user-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--color-text-primary);
}
.logout-btn {
  font-size: 11px;
  color: var(--color-text-muted);
  text-align: left;
  padding: 0;
  margin-top: 2px;
  background: none;
  border: none;
  cursor: pointer;
}
.logout-btn:hover {
  color: var(--color-red);
}

.main {
  flex: 1;
  margin-left: 260px;
  display: flex;
  flex-direction: column;
  min-width: 0;
}
.topbar {
  height: 70px;
  border-bottom: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 32px;
  background-color: var(--color-surface);
  transition: background-color 0.3s ease, border-color 0.3s ease;
}
.page-title {
  font-size: 18px;
  font-weight: 600;
  margin: 0;
  color: var(--color-text-primary);
}
.theme-selector {
  display: flex;
  align-items: center;
  gap: 8px;
  background-color: var(--color-bg);
  border: 1px solid var(--color-border);
  padding: 6px 12px;
  border-radius: 8px;
  transition: all 0.2s ease;
}
.theme-selector:hover {
  border-color: var(--color-primary);
}
.theme-icon {
  width: 16px;
  height: 16px;
  color: var(--color-primary);
}
.theme-select {
  background: transparent;
  border: none;
  color: var(--color-text-primary);
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  outline: none;
  font-family: 'Outfit', sans-serif;
}
.theme-select option {
  background-color: var(--color-surface);
  color: var(--color-text-primary);
}
.content {
  padding: 32px;
  flex: 1;
}
</style>
