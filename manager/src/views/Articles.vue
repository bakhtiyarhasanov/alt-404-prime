<template>
  <div class="articles-view">
    <div class="header-row">
      <div class="filters-wrap">
        <div class="search-box">
          <input v-model="search" type="text" placeholder="Məqalələri axtar..." class="search-input">
        </div>

        <div class="date-filters">
          <div class="date-input-group">
            <span class="date-group-label">Tarixdən:</span>
            <input 
              v-model="startDate" 
              type="date" 
              class="date-input" 
              title="Başlanğıc tarixi"
              @input="activeDatePreset = ''"
            >
          </div>
          <div class="date-input-group">
            <span class="date-group-label">Tarixədək:</span>
            <input 
              v-model="endDate" 
              type="date" 
              class="date-input" 
              title="Son tarix"
              @input="activeDatePreset = ''"
            >
          </div>

          <div class="date-presets">
            <button 
              type="button" 
              class="preset-btn" 
              :class="{ active: activeDatePreset === 'today' }" 
              @click="setPreset('today')"
            >
              Bu gün
            </button>
            <button 
              type="button" 
              class="preset-btn" 
              :class="{ active: activeDatePreset === 'week' }" 
              @click="setPreset('week')"
            >
              Son 7 gün
            </button>
            <button 
              type="button" 
              class="preset-btn" 
              :class="{ active: activeDatePreset === 'month' }" 
              @click="setPreset('month')"
            >
              Bu ay
            </button>
          </div>

          <button 
            v-if="startDate || endDate" 
            @click="clearDateFilter" 
            class="clear-btn" 
            title="Tarix filtrini sıfırla"
          >
            &times; Sıfırla
          </button>
        </div>
      </div>

      <router-link to="/articles/new" class="btn-primary">Yeni Məqalə</router-link>
    </div>

    <!-- Table panel -->
    <div class="panel">
      <table class="table">
        <thead>
          <tr>
            <th>Şəkil</th>
            <th>Başlıq</th>
            <th>Kateqoriya</th>
            <th>Baxış</th>
            <th>Status</th>
            <th>Tarix</th>
            <th class="actions-col">Əməliyyatlar</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="7" class="empty-cell">Məqalələr yüklənir...</td>
          </tr>
          <tr v-else-if="paginatedArticles.length === 0">
            <td colspan="7" class="empty-cell">Heç bir məqalə tapılmadı.</td>
          </tr>
          <tr v-for="art in paginatedArticles" :key="art.id" v-else>
            <td>
              <img :src="getAbsoluteUrl(art.image_url) || 'https://images.pexels.com/photos/1779487/pexels-photo-1779487.jpeg'" class="table-img" alt="">
            </td>
            <td>
              <div class="title-cell">
                <span class="art-title">{{ art.title }}</span>
                <span class="art-slug">{{ art.slug }}</span>
              </div>
            </td>
            <td>
              <span class="cat-badge">{{ getCategoryName(art.category) }}</span>
            </td>
            <td>{{ art.views || 0 }}</td>
            <td>
              <span class="status-badge" :class="getArticleStatus(art).class">
                {{ getArticleStatus(art).text }}
              </span>
            </td>
            <td>{{ formatDate(art.created_at) }}</td>
            <td>
              <div class="actions">
                <router-link :to="'/articles/edit/' + art.id" class="action-btn edit">Redaktə</router-link>
                <button @click="confirmDelete(art)" class="action-btn delete">Sil</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination bar -->
      <div class="pagination" v-if="totalPages > 1">
        <span class="pagination-info">
          Göstərilir: {{ (currentPage - 1) * itemsPerPage + 1 }} - {{ Math.min(currentPage * itemsPerPage, filteredArticles.length) }} / {{ filteredArticles.length }} məqalə (Səhifə {{ currentPage }} / {{ totalPages }})
        </span>
        <div class="pagination-buttons">
          <button 
            :disabled="currentPage === 1" 
            @click="currentPage = 1" 
            class="page-btn first-btn"
            title="İlk səhifə"
          >
            « İlk
          </button>
          <button 
            :disabled="currentPage === 1" 
            @click="currentPage--" 
            class="page-btn prev-btn"
            title="Əvvəlki səhifə"
          >
            &larr; Əvvəlki
          </button>
          <button 
            class="page-btn number-btn active"
            title="Cari səhifə"
          >
            {{ currentPage }}
          </button>
          <button 
            :disabled="currentPage === totalPages" 
            @click="currentPage++" 
            class="page-btn next-btn"
            title="Növbəti səhifə"
          >
            Növbəti &rarr;
          </button>
          <button 
            :disabled="currentPage === totalPages" 
            @click="currentPage = totalPages" 
            class="page-btn last-btn"
            :title="'Son səhifə (' + totalPages + ')'"
          >
            Son ({{ totalPages }}) »
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed, watch } from 'vue'
import client from '../api/client'

export default {
  name: 'ArticlesView',
  setup() {
    const articles = ref([])
    const categories = ref([])
    const search = ref('')
    const startDate = ref('')
    const endDate = ref('')
    const activeDatePreset = ref('')
    const currentPage = ref(1)
    const itemsPerPage = ref(10)
    const loading = ref(false)

    const fetchArticles = async () => {
      loading.value = true
      try {
        const params = {}
        if (startDate.value) params.start_date = startDate.value
        if (endDate.value) params.end_date = endDate.value

        const res = await client.get('/articles', { params })
        articles.value = res.data || []
      } catch (err) {
        console.error('Məqalələr yüklənərkən xəta:', err)
      } finally {
        loading.value = false
      }
    }

    onMounted(async () => {
      try {
        const [catRes] = await Promise.all([
          client.get('/categories'),
          fetchArticles()
        ])
        categories.value = catRes.data || []
      } catch (err) {
        console.error(err)
      }
    })

    const formatDateToYMD = (d) => {
      const year = d.getFullYear()
      const month = String(d.getMonth() + 1).padStart(2, '0')
      const day = String(d.getDate()).padStart(2, '0')
      return `${year}-${month}-${day}`
    }

    const setPreset = (preset) => {
      if (activeDatePreset.value === preset) {
        clearDateFilter()
        return
      }
      const now = new Date()
      const todayStr = formatDateToYMD(now)

      if (preset === 'today') {
        startDate.value = todayStr
        endDate.value = todayStr
        activeDatePreset.value = 'today'
      } else if (preset === 'week') {
        const past = new Date()
        past.setDate(past.getDate() - 6)
        startDate.value = formatDateToYMD(past)
        endDate.value = todayStr
        activeDatePreset.value = 'week'
      } else if (preset === 'month') {
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
        startDate.value = formatDateToYMD(firstDay)
        endDate.value = todayStr
        activeDatePreset.value = 'month'
      }
    }

    const clearDateFilter = () => {
      startDate.value = ''
      endDate.value = ''
      activeDatePreset.value = ''
    }

    // Re-fetch from backend whenever date filters change
    watch([startDate, endDate], () => {
      currentPage.value = 1
      fetchArticles()
    })

    // Reset pagination when searching
    watch(search, () => {
      currentPage.value = 1
    })

    const getAbsoluteUrl = (url) => {
      if (!url) return ''
      if (url.startsWith('/')) {
        return `https://alt404.com${url}`
      }
      return url
    }

    const getCategoryName = (slug) => {
      const cat = categories.value.find(c => c.slug === slug)
      return cat ? cat.label : slug
    }

    const formatDate = (dateStr) => {
      if (!dateStr) return ''
      const d = new Date(dateStr)
      return d.toLocaleDateString('az-AZ', { day: 'numeric', month: 'short', year: 'numeric' })
    }

    const getArticleStatus = (art) => {
      if (!art.published || parseInt(art.published) === 0) {
        return { text: 'Qaralama', class: 'draft' }
      }
      
      const now = new Date()
      if (art.start_time) {
        const start = new Date(art.start_time.replace(' ', 'T'))
        if (start > now) {
          return { text: 'Planlaşdırılıb', class: 'scheduled' }
        }
      }
      if (art.end_time) {
        const end = new Date(art.end_time.replace(' ', 'T'))
        if (end < now) {
          return { text: 'Müddəti bitib', class: 'expired' }
        }
      }
      return { text: 'Dərc edilib', class: 'published' }
    }

    const confirmDelete = async (art) => {
      if (confirm(`"${art.title}" məqaləsini silmək istədiyinizdən əminsiniz?`)) {
        try {
          await client.delete(`/articles/${art.id}`)
          articles.value = articles.value.filter(a => a.id !== art.id)
        } catch (err) {
          alert('Xəbər silinərkən xəta baş verdi')
        }
      }
    }

    const filteredArticles = computed(() => {
      const q = search.value.trim().toLowerCase()
      if (!q) return articles.value
      return articles.value.filter(a => {
        const title = (a.title || '').toLowerCase()
        const slug = (a.slug || '').toLowerCase()
        return title.includes(q) || slug.includes(q)
      })
    })

    const totalPages = computed(() => {
      return Math.ceil(filteredArticles.value.length / itemsPerPage.value) || 1
    })

    const paginatedArticles = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value
      const end = start + itemsPerPage.value
      return filteredArticles.value.slice(start, end)
    })

    return {
      articles,
      search,
      startDate,
      endDate,
      activeDatePreset,
      setPreset,
      clearDateFilter,
      filteredArticles,
      paginatedArticles,
      currentPage,
      itemsPerPage,
      totalPages,
      loading,
      getCategoryName,
      formatDate,
      getArticleStatus,
      confirmDelete,
      getAbsoluteUrl
    }
  }
}
</script>

<style scoped>
.header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  gap: 16px;
  flex-wrap: wrap;
}
.filters-wrap {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
  flex: 1;
}
.search-box {
  flex: 1;
  min-width: 220px;
  max-width: 320px;
}
.search-input {
  width: 100%;
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 9px 14px;
  color: var(--color-text-primary);
  font-family: inherit;
  font-size: 13px;
  outline: none;
  transition: border-color 0.2s ease;
  box-sizing: border-box;
}
.search-input:focus {
  border-color: var(--color-primary);
}
.date-filters {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.date-input-group {
  display: flex;
  align-items: center;
  gap: 6px;
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 5px 10px;
  transition: border-color 0.2s ease;
}
.date-input-group:focus-within {
  border-color: var(--color-primary);
}
.date-group-label {
  font-size: 11px;
  font-weight: 600;
  color: var(--color-text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  white-space: nowrap;
}
.date-input {
  background: transparent;
  border: none;
  color: var(--color-text-primary);
  font-family: inherit;
  font-size: 13px;
  outline: none;
  cursor: pointer;
  color-scheme: dark;
}
:root.theme-light .date-input {
  color-scheme: light;
}
.date-presets {
  display: flex;
  align-items: center;
  gap: 6px;
}
.preset-btn {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  color: var(--color-text-secondary);
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: inherit;
  white-space: nowrap;
}
.preset-btn:hover {
  border-color: var(--color-primary);
  color: var(--color-text-primary);
}
.preset-btn.active {
  background-color: rgba(252, 219, 86, 0.15);
  border-color: var(--color-primary);
  color: var(--color-primary);
  font-weight: 600;
}
.clear-btn {
  background: transparent;
  border: 1px solid var(--color-border);
  color: var(--color-text-muted);
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: all 0.2s ease;
  font-family: inherit;
  white-space: nowrap;
}
.clear-btn:hover {
  border-color: var(--color-red);
  color: var(--color-red);
  background-color: rgba(239, 68, 68, 0.08);
}
.btn-primary {
  background-color: var(--color-primary);
  color: #111;
  font-weight: 600;
  padding: 10px 20px;
  border-radius: 8px;
  text-decoration: none;
  font-size: 14px;
  transition: background-color 0.2s;
}
.btn-primary:hover {
  background-color: var(--color-primary-hover);
}
.panel {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  overflow: hidden;
}
.table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}
.table th, .table td {
  padding: 16px 24px;
  border-bottom: 1px solid var(--color-border);
  font-size: 13px;
}
.table th {
  background-color: rgba(255,255,255,0.02);
  color: var(--color-text-secondary);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: 0.05em;
}
.table-img {
  width: 54px;
  height: 36px;
  border-radius: 6px;
  object-fit: cover;
}
.title-cell {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.art-title {
  font-weight: 600;
  color: var(--color-text-primary);
  font-size: 13px;
}
.art-slug {
  font-size: 11px;
  color: var(--color-text-muted);
}
.cat-badge {
  background-color: rgba(255,255,255,0.05);
  border: 1px solid var(--color-border);
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
}
.status-badge {
  font-size: 11px;
  font-weight: 600;
  color: var(--color-text-muted);
}
.status-badge.published {
  color: var(--color-green);
}
.status-badge.draft {
  color: var(--color-text-muted);
}
.status-badge.scheduled {
  color: var(--color-primary);
}
.status-badge.expired {
  color: var(--color-red);
}
.actions {
  display: flex;
  align-items: center;
  gap: 8px;
}
.action-btn {
  font-size: 12px;
  padding: 6px 12px;
  border-radius: 6px;
  border: 1px solid var(--color-border);
  cursor: pointer;
  background: none;
  font-family: inherit;
  transition: all 0.2s;
  text-decoration: none;
}
.action-btn.edit {
  color: var(--color-primary);
}
.action-btn.edit:hover {
  background-color: rgba(252, 219, 86, 0.05);
}
.action-btn.delete {
  color: var(--color-red);
}
.action-btn.delete:hover {
  background-color: rgba(239, 68, 68, 0.05);
}
.empty-cell {
  text-align: center;
  color: var(--color-text-muted);
  padding: 48px;
}

/* Pagination Bar Styles */
.pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  background-color: var(--color-surface);
  border-top: 1px solid var(--color-border);
  flex-wrap: wrap;
  gap: 16px;
}
.pagination-info {
  font-size: 13px;
  color: var(--color-text-secondary);
}
.pagination-buttons {
  display: flex;
  align-items: center;
  gap: 6px;
}
.page-btn {
  background-color: var(--color-bg);
  border: 1px solid var(--color-border);
  color: var(--color-text-primary);
  padding: 8px 14px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  user-select: none;
}
.page-btn:hover:not(:disabled) {
  border-color: var(--color-primary);
  color: var(--color-primary);
  transform: translateY(-1px);
}
.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.page-btn.active {
  background-color: var(--color-primary);
  border-color: var(--color-primary);
  color: #111;
  font-weight: 600;
}
</style>
