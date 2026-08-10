<template>
  <div class="dashboard">
    <!-- Stat grid -->
    <div class="stats">
      <div class="card">
        <span class="card-label">Ümumi Məqalələr</span>
        <span class="card-val">{{ articles.length }}</span>
      </div>
      <div class="card">
        <span class="card-label">Oxunma Sayı</span>
        <span class="card-val">{{ totalViews }}</span>
      </div>
      <div class="card">
        <span class="card-label">Müraciətlər</span>
        <span class="card-val">{{ newContacts }} / {{ contacts.length }}</span>
      </div>
      <div class="card">
        <span class="card-label">Videolar</span>
        <span class="card-val">{{ videos.length }}</span>
      </div>
    </div>

    <!-- Verification Queue -->
    <div class="panel verification-panel" v-if="pendingArticles.length > 0">
      <div class="panel-header">
        <h2 class="panel-title alert-title">Təsdiq Gözləyən Məqalələr ({{ pendingArticles.length }})</h2>
      </div>
      <div class="list">
        <div v-for="art in pendingArticles" :key="art.id" class="list-item alert-item">
          <img :src="art.image_url || 'https://images.pexels.com/photos/1779487/pexels-photo-1779487.jpeg'" class="item-img" alt="">
          <div class="item-info">
            <span class="item-title">{{ art.title }}</span>
            <span class="item-sub">Müəllif: {{ art.creator_name || 'Bilinmir' }} • {{ art.category }}</span>
          </div>
          <div class="actions">
            <router-link :to="'/articles/edit/' + art.id" class="edit-btn">Baxış / Redaktə</router-link>
            <button v-if="user.role === 'editor'" @click="approveArticle(art)" class="approve-btn">Təsdiqlə</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick view panels -->
    <div class="grid">
      <!-- Recent articles -->
      <div class="panel">
        <div class="panel-header">
          <h2 class="panel-title">Son Məqalələr</h2>
          <router-link to="/articles" class="panel-link">Hamısına Bax</router-link>
        </div>
        <div class="list">
          <div v-for="art in recentArticles" :key="art.id" class="list-item">
            <img :src="art.image_url || 'https://images.pexels.com/photos/1779487/pexels-photo-1779487.jpeg'" class="item-img" alt="">
            <div class="item-info">
              <span class="item-title">{{ art.title }}</span>
              <span class="item-sub">{{ art.category }} • {{ art.views }} baxış • {{ art.creator_name || 'Bilinmir' }}</span>
            </div>
            <router-link :to="'/articles/edit/' + art.id" class="edit-btn">Redaktə</router-link>
          </div>
        </div>
      </div>

      <!-- Recent submissions -->
      <div class="panel">
        <div class="panel-header">
          <h2 class="panel-title">Son Müraciətlər</h2>
          <router-link to="/contacts" class="panel-link">Hamısına Bax</router-link>
        </div>
        <div class="list">
          <div v-for="c in recentContacts" :key="c.id" class="list-item">
            <div class="item-info">
              <span class="item-title">{{ c.ad_soyad }} ({{ c.email }})</span>
              <p class="item-excerpt">{{ c.mesaj }}</p>
            </div>
            <span class="status-badge" :class="c.status">{{ c.status === 'new' ? 'Yeni' : 'Baxılıb' }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import client from '../api/client'
import { useAuthStore } from '../stores/auth'

export default {
  name: 'DashboardView',
  setup() {
    const authStore = useAuthStore()
    const articles = ref([])
    const contacts = ref([])
    const videos = ref([])

    const fetchDashboardData = async () => {
      try {
        const [artRes, conRes, vidRes] = await Promise.all([
          client.get('/articles'),
          client.get('/contacts'),
          client.get('/videos')
        ])
        articles.value = artRes.data
        contacts.value = conRes.data
        videos.value = vidRes.data
      } catch (err) {
        console.error(err)
      }
    }

    onMounted(fetchDashboardData)

    const totalViews = computed(() => {
      return articles.value.reduce((acc, a) => acc + (a.views || 0), 0)
    })

    const newContacts = computed(() => {
      return contacts.value.filter(c => c.status === 'new').length
    })

    const approveArticle = async (art) => {
      try {
        await client.put(`/articles/${art.id}`, {
          ...art,
          published: true
        })
        await fetchDashboardData()
      } catch (err) {
        alert(err.response?.data?.error || 'Təsdiqləmə zamanı xəta baş verdi.')
      }
    }

    return {
      articles,
      contacts,
      videos,
      totalViews,
      newContacts,
      user: computed(() => authStore.user || {}),
      recentArticles: computed(() => articles.value.slice(0, 5)),
      recentContacts: computed(() => contacts.value.slice(0, 5)),
      pendingArticles: computed(() => articles.value.filter(a => !a.published)),
      approveArticle
    }
  }
}
</script>

<style scoped>
.stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 32px;
}
.card {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.card-label {
  font-size: 12px;
  color: var(--color-text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
}
.card-val {
  font-size: 28px;
  font-weight: 700;
  color: var(--color-text-primary);
}
.grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 24px;
}
.panel {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 24px;
}
.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}
.panel-title {
  font-size: 15px;
  font-weight: 600;
  margin: 0;
  color: var(--color-text-primary);
}
.panel-link {
  font-size: 12px;
  color: var(--color-primary);
  text-decoration: none;
}
.list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.list-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--color-border);
}
.list-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}
.item-img {
  width: 48px;
  height: 36px;
  border-radius: 6px;
  object-fit: cover;
}
.item-info {
  flex: 1;
  min-width: 0;
}
.item-title {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: var(--color-text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.item-sub {
  font-size: 11px;
  color: var(--color-text-secondary);
}
.item-excerpt {
  font-size: 12px;
  color: var(--color-text-secondary);
  margin: 4px 0 0;
  display: -webkit-box;
  line-clamp: 2;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.edit-btn {
  font-size: 12px;
  color: var(--color-primary);
  border: 1px solid var(--color-border);
  padding: 6px 12px;
  border-radius: 6px;
  text-decoration: none;
  background-color: var(--color-bg);
}
.status-badge {
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: 600;
}
.status-badge.new {
  background-color: rgba(239, 68, 68, 0.15);
  color: var(--color-red);
}
.status-badge.reviewed {
  background-color: rgba(16, 185, 129, 0.15);
  color: var(--color-green);
}

.verification-panel {
  margin-bottom: 24px;
  border-color: var(--color-primary);
}
.alert-title {
  color: var(--color-primary);
  display: flex;
  align-items: center;
  gap: 8px;
}
.alert-item {
  border-bottom: 1px solid var(--color-border);
}
.approve-btn {
  font-size: 12px;
  color: #111;
  background-color: var(--color-primary);
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
}
.approve-btn:hover {
  opacity: 0.9;
}
.actions {
  display: flex;
  gap: 8px;
}
</style>
