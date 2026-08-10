<template>
  <div class="articles-view">
    <div class="header-row">
      <div class="search-box">
        <input v-model="search" type="text" placeholder="Məqalələri axtar..." class="search-input">
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
          <tr v-for="art in paginatedArticles" :key="art.id">
            <td>
              <img :src="art.image_url || 'https://images.pexels.com/photos/1779487/pexels-photo-1779487.jpeg'" class="table-img" alt="">
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
          <tr v-if="paginatedArticles.length === 0">
            <td colspan="7" class="empty-cell">Heç bir məqalə tapılmadı.</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination bar -->
      <div class="pagination" v-if="totalPages > 1">
        <span class="pagination-info">
          Göstərilir: {{ (currentPage - 1) * itemsPerPage + 1 }} - {{ Math.min(currentPage * itemsPerPage, filteredArticles.length) }} / {{ filteredArticles.length }} məqalə
        </span>
        <div class="pagination-buttons">
          <button :disabled="currentPage === 1" @click="currentPage--" class="page-btn prev-btn">
            &larr; Əvvəlki
          </button>
          <button 
            v-for="page in totalPages" 
            :key="page" 
            @click="currentPage = page" 
            class="page-btn number-btn"
            :class="{ active: currentPage === page }"
          >
            {{ page }}
          </button>
          <button :disabled="currentPage === totalPages" @click="currentPage++" class="page-btn next-btn">
            Növbəti &rarr;
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
    const currentPage = ref(1)
    const itemsPerPage = ref(10)

    onMounted(async () => {
      try {
        const [artRes, catRes] = await Promise.all([
          client.get('/articles'),
          client.get('/categories')
        ])
        articles.value = artRes.data
        categories.value = catRes.data
      } catch (err) {
        console.error(err)
      }
    })

    // Reset pagination when searching
    watch(search, () => {
      currentPage.value = 1
    })

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
      return articles.value.filter(a => 
        a.title.toLowerCase().includes(q) || 
        a.slug.toLowerCase().includes(q)
      )
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
      filteredArticles,
      paginatedArticles,
      currentPage,
      itemsPerPage,
      totalPages,
      getCategoryName,
      formatDate,
      getArticleStatus,
      confirmDelete
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
}
.search-box {
  flex: 1;
  max-width: 360px;
}
.search-input {
  width: 100%;
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 10px 16px;
  color: var(--color-text-primary);
  font-family: inherit;
  outline: none;
  transition: border-color 0.2s ease;
}
.search-input:focus {
  border-color: var(--color-primary);
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
