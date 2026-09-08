<template>
  <div class="news-feed-page">
    <div class="page-header">
      <div>
        <h1>Toplanan Xəbərlər</h1>
        <p class="page-desc">Mənbələrdən çəkilmiş xəbərlərin siyahısı, təkrar xəbərlərin idarəsi və qaralama statusları.</p>
      </div>

      <div class="header-actions">
        <button 
          @click="store.batchProcess()" 
          :disabled="store.loading || store.stats.new === 0" 
          class="btn btn-primary"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
          </svg>
          <span>Növbəti 5 Yeni Xəbəri Qaralama Et</span>
        </button>
      </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="filter-card">
      <div class="status-pills">
        <button 
          @click="setStatusFilter('all')" 
          class="pill-btn" 
          :class="{ active: store.filters.status === 'all' }"
        >
          Hamısı ({{ store.stats.total }})
        </button>
        <button 
          @click="setStatusFilter('new')" 
          class="pill-btn" 
          :class="{ active: store.filters.status === 'new' }"
        >
          Yeni ({{ store.stats.new }})
        </button>
        <button 
          @click="setStatusFilter('posted')" 
          class="pill-btn" 
          :class="{ active: store.filters.status === 'posted' }"
        >
          Paylaşılanlar ({{ store.stats.posted }})
        </button>
        <button 
          @click="setStatusFilter('duplicate')" 
          class="pill-btn" 
          :class="{ active: store.filters.status === 'duplicate' }"
        >
          Dublikatlar ({{ store.stats.duplicate }})
        </button>
        <button 
          @click="setStatusFilter('error')" 
          class="pill-btn" 
          :class="{ active: store.filters.status === 'error' }"
        >
          Xətalar ({{ store.stats.error }})
        </button>
      </div>

      <div class="search-and-source-row">
        <!-- Search Input -->
        <div class="search-box">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input 
            type="text" 
            placeholder="Başlıq üzrə axtarış..." 
            v-model="searchInput" 
            @keyup.enter="applySearch"
          />
          <button v-if="searchInput" @click="clearSearch" class="btn-clear">&times;</button>
        </div>

        <!-- Source Filter Dropdown -->
        <select v-model="store.filters.source_id" @change="applyFilters" class="filter-select">
          <option value="all">Bütün Mənbələr</option>
          <option v-for="src in store.sources" :key="src.id" :value="src.id">{{ src.name }}</option>
        </select>
      </div>
    </div>

    <!-- News List -->
    <div v-if="store.loading && store.news.length === 0" class="loading-box">
      <div class="spinner"></div>
      <span>Xəbərlər yüklənir...</span>
    </div>

    <div v-else-if="store.news.length === 0" class="empty-news-box">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/>
        <path d="M18 14h-8"/>
      </svg>
      <h3>Heç bir xəbər tapılmadı</h3>
      <p>Axtarış filtrini dəyişin və ya yuxarıdakı "Mənbələri Yoxla" düyməsinə klikləyərək yeni xəbərləri toplayın.</p>
    </div>

    <div v-else class="news-cards-list">
      <div 
        v-for="item in store.news" 
        :key="item.id" 
        class="news-card" 
        :class="{ 'card-duplicate': item.is_duplicate, 'card-posted': item.status === 'posted' }"
      >
        <!-- Thumbnail -->
        <div class="news-thumb-wrap" @click="openModal(item)">
          <img v-if="item.source_image_url" :src="item.source_image_url" alt="News thumb" class="news-thumb" loading="lazy" />
          <div v-else class="news-thumb-placeholder">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
              <circle cx="8.5" cy="8.5" r="1.5"/>
              <polyline points="21 15 16 10 5 21"/>
            </svg>
          </div>
        </div>

        <!-- News Details -->
        <div class="news-content">
          <div class="news-meta-top">
            <span :class="'badge-' + item.status">{{ formatStatus(item.status) }}</span>
            <span class="news-source-tag">{{ item.source_name }}</span>
            <span v-if="item.is_duplicate" class="badge-dup-small">Təkrar Xəbər</span>
            <span class="news-date">{{ formatDate(item.created_at) }}</span>
          </div>

          <h3 class="news-title" @click="openModal(item)">
            {{ item.rewritten_title || item.source_title }}
          </h3>

          <p v-if="item.rewritten_title && item.source_title !== item.rewritten_title" class="news-original-title">
            <strong>Orijinal:</strong> {{ item.source_title }}
          </p>

          <p class="news-excerpt">
            {{ item.rewritten_excerpt || item.source_excerpt || 'Mətn xülasəsi yoxdur.' }}
          </p>

          <div v-if="item.status_message" class="news-status-msg" :class="item.status === 'error' ? 'msg-error' : 'msg-info'">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>{{ item.status_message }}</span>
          </div>
        </div>

        <!-- Actions Column -->
        <div class="news-card-actions">
          <button 
            v-if="item.status === 'new'" 
            @click="store.processNewsItem(item.id)" 
            :disabled="store.processingId === item.id"
            class="btn btn-primary btn-sm"
          >
            <span v-if="store.processingId === item.id">Yazılır...</span>
            <span v-else>Qaralama Et</span>
          </button>

          <button 
            v-if="item.status === 'posted'" 
            class="btn btn-cyan btn-sm" 
            @click="openModal(item)"
          >
            Drafta Bax
          </button>

          <button 
            v-if="item.status === 'posted'" 
            class="btn btn-warning btn-sm" 
            @click="store.regenerateNewsItem(item.id)"
            :disabled="store.processingId === item.id"
            title="OpenAI ilə yenidən generasiya et"
          >
            <span v-if="store.processingId === item.id">Yazılır...</span>
            <span v-else>Yenidən Yaz</span>
          </button>

          <button @click="openModal(item)" class="btn btn-secondary btn-sm">Müqayisə</button>

          <button 
            @click="store.toggleDuplicate(item.id)" 
            class="btn-icon" 
            :title="item.is_duplicate ? 'Təkrar statusunu çıxar' : 'Təkrar kimi qeyd et'"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" :stroke="item.is_duplicate ? '#f59e0b' : 'currentColor'" stroke-width="2">
              <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
            </svg>
          </button>

          <button 
            @click="store.deleteNews(item.id)" 
            class="btn-icon btn-delete" 
            title="Sil"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Pagination Controls -->
    <div v-if="store.totalPages > 1" class="pagination-bar">
      <button 
        :disabled="store.currentPage <= 1" 
        @click="store.fetchNews(store.currentPage - 1)" 
        class="btn btn-secondary btn-sm"
      >
        Əvvəlki
      </button>

      <span class="page-indicator">Səhifə {{ store.currentPage }} / {{ store.totalPages }} (Toplam {{ store.totalNews }})</span>

      <button 
        :disabled="store.currentPage >= store.totalPages" 
        @click="store.fetchNews(store.currentPage + 1)" 
        class="btn btn-secondary btn-sm"
      >
        Növbəti
      </button>
    </div>

    <!-- Detail Modal -->
    <ArticleModal v-if="selectedArticle" :article="selectedArticle" @close="selectedArticle = null" />
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useAiWriterStore } from '../stores/aiWriter'
import { useRoute } from 'vue-router'
import ArticleModal from '../components/ArticleModal.vue'

export default {
  name: 'NewsFeed',
  components: { ArticleModal },
  setup() {
    const store = useAiWriterStore()
    const route = useRoute()
    const searchInput = ref('')
    const selectedArticle = ref(null)

    onMounted(async () => {
      await store.fetchSources()
      if (route.query.status) {
        store.filters.status = route.query.status
      }
      await store.fetchNews(1)
    })

    const setStatusFilter = (st) => {
      store.filters.status = st
      store.fetchNews(1)
    }

    const applySearch = () => {
      store.filters.search = searchInput.value
      store.fetchNews(1)
    }

    const clearSearch = () => {
      searchInput.value = ''
      store.filters.search = ''
      store.fetchNews(1)
    }

    const applyFilters = () => {
      store.fetchNews(1)
    }

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

    const openModal = (art) => {
      selectedArticle.value = art
      store.activeArticle = art
    }

    return {
      store,
      searchInput,
      selectedArticle,
      setStatusFilter,
      applySearch,
      clearSearch,
      applyFilters,
      formatStatus,
      formatDate,
      openModal
    }
  }
}
</script>

<style scoped>
.news-feed-page {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.page-header h1 {
  font-size: 1.6rem;
  font-weight: 700;
  color: #fff;
}

.page-desc {
  font-size: 0.9rem;
  color: var(--text-muted);
  margin-top: 4px;
}

/* Filter Card */
.filter-card {
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-lg);
  padding: 18px 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.status-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.pill-btn {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: var(--text-muted);
  padding: 7px 16px;
  border-radius: var(--radius-full);
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pill-btn:hover {
  background: rgba(255, 255, 255, 0.08);
  color: #fff;
}

.pill-btn.active {
  background: var(--color-primary);
  color: #07090e;
  border-color: var(--color-primary);
}

.search-and-source-row {
  display: flex;
  align-items: center;
  gap: 16px;
}

.search-box {
  flex: 1;
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-md);
  padding: 8px 14px;
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--text-muted);
}

.search-box input {
  flex: 1;
  background: transparent;
  border: none;
  color: #fff;
  font-size: 0.88rem;
  outline: none;
}

.btn-clear {
  background: transparent;
  border: none;
  color: var(--text-dim);
  font-size: 1.2rem;
  cursor: pointer;
}

.filter-select {
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  color: #fff;
  padding: 10px 16px;
  border-radius: var(--radius-md);
  font-size: 0.85rem;
  outline: none;
  cursor: pointer;
}

/* News List */
.news-cards-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.news-card {
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-md);
  padding: 16px 20px;
  display: flex;
  align-items: center;
  gap: 20px;
  transition: all 0.2s ease;
}

.news-card:hover {
  border-color: rgba(255, 255, 255, 0.15);
  background: var(--bg-card-hover);
}

.card-duplicate {
  border-left: 4px solid var(--status-duplicate);
  background: rgba(245, 158, 11, 0.03);
}

.card-posted {
  border-left: 4px solid var(--status-posted);
}

/* Thumb */
.news-thumb-wrap {
  width: 110px;
  height: 80px;
  border-radius: var(--radius-sm);
  overflow: hidden;
  flex-shrink: 0;
  cursor: pointer;
  background: var(--bg-surface);
}

.news-thumb {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.news-thumb-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-dim);
}

/* Content */
.news-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.news-meta-top {
  display: flex;
  align-items: center;
  gap: 10px;
}

.news-source-tag {
  color: var(--color-primary);
  font-size: 0.78rem;
  font-weight: 600;
}

.badge-dup-small {
  font-size: 0.7rem;
  color: var(--status-duplicate);
  background: rgba(245, 158, 11, 0.1);
  padding: 1px 6px;
  border-radius: 4px;
}

.news-date {
  font-size: 0.75rem;
  color: var(--text-dim);
  margin-left: auto;
}

.news-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: #fff;
  cursor: pointer;
  line-height: 1.35;
}
.news-title:hover {
  color: var(--color-primary);
}

.news-original-title {
  font-size: 0.8rem;
  color: var(--text-dim);
}

.news-excerpt {
  font-size: 0.85rem;
  color: var(--text-muted);
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.news-status-msg {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.78rem;
  padding: 4px 8px;
  border-radius: 4px;
  margin-top: 4px;
}
.msg-info { background: rgba(56, 189, 248, 0.08); color: var(--status-new); }
.msg-error { background: rgba(239, 68, 68, 0.08); color: var(--status-error); }

/* Card Actions */
.news-card-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
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

.btn-icon {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--border-subtle);
  color: var(--text-muted);
  width: 34px;
  height: 34px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-icon:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
}

.btn-delete:hover {
  color: #ef4444;
  border-color: rgba(239, 68, 68, 0.4);
}

/* Pagination */
.pagination-bar {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  margin-top: 10px;
}

.page-indicator {
  font-size: 0.85rem;
  color: var(--text-muted);
}

.loading-box, .empty-news-box {
  text-align: center;
  padding: 60px 20px;
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-lg);
  color: var(--text-muted);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}
</style>
