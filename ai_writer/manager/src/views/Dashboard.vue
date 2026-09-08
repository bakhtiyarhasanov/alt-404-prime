<template>
  <div class="dashboard-page">
    <!-- Welcome / Header Banner -->
    <div class="welcome-banner">
      <div class="banner-content">
        <h1>Süni İntellekt Jurnalisti və Xəbər İdarəetməsi</h1>
        <p>12 texnoloji və rəsmi mənbədən xəbərlərin avtomatik toplanması, dublikatların filtirlənməsi və Azərbaycan dilinə adaptasiyası.</p>
      </div>
      <div class="banner-quick-actions">
        <button @click="store.grabAllSources()" :disabled="store.loading" class="btn btn-secondary">
          <svg class="spin-on-load" :class="{ spinning: store.loading }" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
          </svg>
          <span>Bütün Mənbələri Topla</span>
        </button>
        <button @click="store.batchProcess()" :disabled="store.loading || store.stats.new === 0" class="btn btn-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
          </svg>
          <span>Yeni Xəbərləri Qaralamaya Göndər ({{ store.stats.new }})</span>
        </button>
      </div>
    </div>

    <!-- Metrics Grid -->
    <div class="metrics-grid">
      <!-- Total Grabbed -->
      <div class="metric-card">
        <div class="metric-header">
          <span class="metric-label">Ümumi Toplanmış</span>
          <div class="metric-icon icon-cyan">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
          </div>
        </div>
        <div class="metric-value">{{ store.stats.total }}</div>
        <div class="metric-footer">12 mənbə üzrə</div>
      </div>

      <!-- New / Pending -->
      <div class="metric-card" @click="$router.push('/news?status=new')">
        <div class="metric-header">
          <span class="metric-label">Yeni (Gözləyən)</span>
          <div class="metric-icon icon-blue">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/>
            </svg>
          </div>
        </div>
        <div class="metric-value text-blue">{{ store.stats.new }}</div>
        <div class="metric-footer">AI Rewrite və Qaralama üçün hazır</div>
      </div>

      <!-- Posted to Web Draft -->
      <div class="metric-card" @click="$router.push('/news?status=posted')">
        <div class="metric-header">
          <span class="metric-label">Paylaşılan Qaralamalar</span>
          <div class="metric-icon icon-green">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
          </div>
        </div>
        <div class="metric-value text-green">{{ store.stats.posted }}</div>
        <div class="metric-footer">alt404.az saytında qaralama kimi</div>
      </div>

      <!-- Duplicates Blocked -->
      <div class="metric-card" @click="$router.push('/news?status=duplicate')">
        <div class="metric-header">
          <span class="metric-label">Dublikatlar (Təkrar)</span>
          <div class="metric-icon icon-yellow">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
            </svg>
          </div>
        </div>
        <div class="metric-value text-yellow">{{ store.stats.duplicate }}</div>
        <div class="metric-footer">Avtomatik bloklanmış təkrar xəbərlər</div>
      </div>

      <!-- Errors -->
      <div class="metric-card" @click="$router.push('/news?status=error')">
        <div class="metric-header">
          <span class="metric-label">Xətalar</span>
          <div class="metric-icon icon-red">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
          </div>
        </div>
        <div class="metric-value text-red">{{ store.stats.error }}</div>
        <div class="metric-footer">Yenidən cəhd etmək mümkündür</div>
      </div>
    </div>

    <!-- Active Sources Quick Overview -->
    <div class="section-card">
      <div class="section-header">
        <div>
          <h2>Aktiv Mənbələr və Statuslar</h2>
          <p class="section-desc">12 saytdan ibarət aqreqator şəbəkəsi</p>
        </div>
        <router-link to="/sources" class="btn btn-secondary btn-sm">Mənbələri İdarə Et</router-link>
      </div>

      <div class="sources-quick-grid">
        <div v-for="src in store.sources" :key="src.id" class="src-mini-card">
          <div class="src-mini-header">
            <span class="src-dot" :class="src.is_enabled ? 'dot-active' : 'dot-disabled'"></span>
            <span class="src-name">{{ src.name }}</span>
            <span v-if="!src.rewrite_enabled" class="tag-gov">Rəsmi / Kopyalanır</span>
            <span v-else class="tag-ai">AI Rewrite</span>
          </div>
          <div class="src-mini-body">
            <div class="src-stat">
              <span>İnterval:</span>
              <strong>{{ src.retry_interval_minutes }} dəq</strong>
            </div>
            <div class="src-stat">
              <span>Xəbərlər:</span>
              <strong>{{ src.total_news || 0 }}</strong>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Latest News Feed Preview -->
    <div class="section-card">
      <div class="section-header">
        <div>
          <h2>Son Əlavə Olunan Xəbərlər</h2>
          <p class="section-desc">Ən son toplanmış və ya generasiya edilmiş materiallar</p>
        </div>
        <router-link to="/news" class="btn btn-secondary btn-sm">Bütün Xəbərlərə Bax</router-link>
      </div>

      <div v-if="recentNews.length === 0" class="empty-state">
        <p>Hələ heç bir xəbər toplanmayıb. "Bütün Mənbələri Topla" düyməsinə klikləyərək ilk xəbərləri əldə edin.</p>
      </div>

      <div v-else class="recent-news-list">
        <div v-for="item in recentNews" :key="item.id" class="recent-item">
          <div class="item-status">
            <span :class="'badge-' + item.status">{{ formatStatus(item.status) }}</span>
          </div>
          <div class="item-info">
            <h4 class="item-title">{{ item.rewritten_title || item.source_title }}</h4>
            <div class="item-meta">
              <span class="meta-source">{{ item.source_name }}</span>
              <span class="meta-date">{{ formatDate(item.created_at) }}</span>
            </div>
          </div>
          <div class="item-actions">
            <button v-if="item.status === 'new'" @click="store.processNewsItem(item.id)" :disabled="store.processingId === item.id" class="btn btn-primary btn-sm">
              <span v-if="store.processingId === item.id">Yazılır...</span>
              <span v-else>Qaralama Et</span>
            </button>
            <button v-if="item.status === 'posted'" @click="store.regenerateNewsItem(item.id)" :disabled="store.processingId === item.id" class="btn btn-warning btn-sm" title="OpenAI ilə yenidən yaz">
              <span v-if="store.processingId === item.id">Yazılır...</span>
              <span v-else>Yenidən Yaz</span>
            </button>
            <button @click="openModal(item)" class="btn btn-secondary btn-sm">Bax</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Article Preview Modal -->
    <ArticleModal v-if="selectedArticle" :article="selectedArticle" @close="selectedArticle = null" />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useAiWriterStore } from '../stores/aiWriter'
import ArticleModal from '../components/ArticleModal.vue'

export default {
  name: 'Dashboard',
  components: { ArticleModal },
  setup() {
    const store = useAiWriterStore()
    const selectedArticle = ref(null)

    onMounted(async () => {
      await store.fetchSources()
      await store.fetchNews(1)
    })

    const recentNews = computed(() => store.news.slice(0, 6))

    const formatStatus = (st) => {
      const map = {
        new: 'Yeni',
        posted: 'Paylaşıldı (Draft)',
        duplicate: 'Dublikat',
        error: 'Xəta',
        generating: 'Yazılır...'
      }
      return map[st] || st
    }

    const formatDate = (d) => {
      if (!d) return ''
      return new Date(d).toLocaleString('az-AZ', {
        month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
      })
    }

    const openModal = (article) => {
      selectedArticle.value = article
      store.activeArticle = article
    }

    return {
      store,
      recentNews,
      selectedArticle,
      formatStatus,
      formatDate,
      openModal
    }
  }
}
</script>

<style scoped>
.dashboard-page {
  display: flex;
  flex-direction: column;
  gap: 28px;
}

/* Welcome Banner */
.welcome-banner {
  background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-surface) 100%);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-lg);
  padding: 32px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  position: relative;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}

.welcome-banner::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -20%;
  width: 350px;
  height: 350px;
  background: radial-gradient(circle, rgba(0, 240, 255, 0.08) 0%, transparent 70%);
  pointer-events: none;
}

.banner-content h1 {
  font-size: 1.6rem;
  font-weight: 700;
  margin-bottom: 8px;
  color: var(--text-main);
}

.banner-content p {
  color: var(--text-muted);
  font-size: 0.95rem;
  max-width: 600px;
}

.banner-quick-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
}

/* Metrics Grid */
.metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 18px;
}

.metric-card {
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-md);
  padding: 22px;
  cursor: pointer;
  transition: all 0.25s ease;
  box-shadow: var(--shadow-sm);
}

.metric-card:hover {
  background: var(--bg-card-hover);
  border-color: var(--border-glow);
  transform: translateY(-2px);
}

.metric-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.metric-icon {
  width: 38px;
  height: 38px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
}

.metric-title {
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.metric-value {
  font-size: 2.1rem;
  font-weight: 800;
  color: var(--text-main);
  line-height: 1;
  margin-bottom: 6px;
}

.metric-sub {
  font-size: 0.76rem;
  color: var(--text-dim);
}

.icon-cyan { background: rgba(0, 240, 255, 0.1); color: var(--color-accent); }
.icon-blue { background: rgba(56, 189, 248, 0.1); color: var(--status-new); }
.icon-green { background: rgba(16, 185, 129, 0.1); color: var(--status-posted); }
.icon-yellow { background: rgba(245, 158, 11, 0.1); color: var(--status-duplicate); }
.icon-red { background: rgba(239, 68, 68, 0.1); color: var(--status-error); }

.text-blue { color: var(--status-new); }
.text-green { color: var(--status-posted); }
.text-yellow { color: var(--status-duplicate); }
.text-red { color: var(--status-error); }

/* Dashboard Sections */
.dashboard-card {
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-lg);
  padding: 24px;
  box-shadow: var(--shadow-card);
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.section-header h2 {
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--text-main);
}

.section-desc {
  font-size: 0.82rem;
  color: var(--text-muted);
  margin-top: 2px;
}

/* Sources Quick Grid */
.sources-quick-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 14px;
}

.src-mini-card {
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-sm);
  padding: 14px;
}

.src-mini-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 10px;
}

.src-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}
.dot-active { background: #10b981; box-shadow: 0 0 8px #10b981; }
.dot-disabled { background: #64748b; }

.src-name {
  font-size: 0.88rem;
  font-weight: 600;
  color: var(--text-main);
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.tag-gov {
  font-size: 0.68rem;
  background: rgba(16, 185, 129, 0.15);
  color: #10b981;
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: 600;
}

.tag-ai {
  font-size: 0.68rem;
  background: rgba(0, 240, 255, 0.15);
  color: var(--color-accent);
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: 600;
}

.src-mini-body {
  display: flex;
  justify-content: space-between;
  font-size: 0.78rem;
  color: var(--text-muted);
}

/* Recent News */
.recent-news-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.recent-item {
  display: flex;
  align-items: center;
  gap: 16px;
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  padding: 14px 18px;
  border-radius: var(--radius-sm);
  transition: all 0.2s ease;
}

.recent-item:hover {
  border-color: var(--border-glow);
  background: var(--bg-card-hover);
}

.item-info {
  flex: 1;
}

.item-title {
  font-size: 0.92rem;
  font-weight: 600;
  color: #fff;
  margin-bottom: 4px;
}

.item-meta {
  font-size: 0.78rem;
  color: var(--text-dim);
  display: flex;
  gap: 12px;
}

.meta-source {
  color: var(--color-primary);
  font-weight: 500;
}

.item-actions {
  display: flex;
  gap: 8px;
}

/* Status Badges */
.badge-new {
  background: rgba(56, 189, 248, 0.15);
  color: var(--status-new);
  border: 1px solid rgba(56, 189, 248, 0.3);
  padding: 3px 9px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-posted {
  background: rgba(16, 185, 129, 0.15);
  color: var(--status-posted);
  border: 1px solid rgba(16, 185, 129, 0.3);
  padding: 3px 9px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-duplicate {
  background: rgba(245, 158, 11, 0.15);
  color: var(--status-duplicate);
  border: 1px solid rgba(245, 158, 11, 0.3);
  padding: 3px 9px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-error {
  background: rgba(239, 68, 68, 0.15);
  color: var(--status-error);
  border: 1px solid rgba(239, 68, 68, 0.3);
  padding: 3px 9px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-generating {
  background: rgba(168, 85, 247, 0.15);
  color: var(--status-generating);
  border: 1px solid rgba(168, 85, 247, 0.3);
  padding: 3px 9px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.btn-warning {
  background: #f59e0b;
  color: #0f172a;
  font-weight: 600;
  border: none;
}
.btn-warning:hover:not(:disabled) {
  background: #fbbf24;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: var(--text-dim);
  font-size: 0.95rem;
}
</style>
