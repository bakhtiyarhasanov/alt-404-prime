<template>
  <div class="grab-history-page">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1>Toplanış Tarixçəsi (Grabber Logs)</h1>
        <p class="page-desc">
          Bütün mənbələr üzrə aqreqatorun icra vaxtı, toplanan xəbərlər və DB-yə əlavə edilən yeni xəbərlərin tam tarixçəsi.
        </p>
      </div>
      <div class="page-actions">
        <button @click="refreshHistory" :disabled="store.grabHistoryLoading" class="btn btn-secondary" title="Tarixçəni yenilə">
          <svg class="spin-on-load" :class="{ spinning: store.grabHistoryLoading }" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
          </svg>
          <span>Yenilə</span>
        </button>
        <button @click="store.grabAllSources()" :disabled="store.loading" class="btn btn-primary">
          <svg class="spin-on-load" :class="{ spinning: store.loading }" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
          </svg>
          <span>Hamısını İndi Topla</span>
        </button>
      </div>
    </div>

    <!-- Summary Metrics -->
    <div class="metrics-grid">
      <div class="metric-card">
        <div class="metric-header">
          <span class="metric-label">Toplam İcra Sayı</span>
          <div class="metric-icon icon-cyan">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/>
            </svg>
          </div>
        </div>
        <div class="metric-value">{{ store.grabHistoryStats.total_runs || store.grabHistoryTotal }}</div>
        <div class="metric-footer">Toplama əməliyyatları</div>
      </div>

      <div class="metric-card">
        <div class="metric-header">
          <span class="metric-label">Toplanan Xəbərlər</span>
          <div class="metric-icon icon-blue">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Z"/>
            </svg>
          </div>
        </div>
        <div class="metric-value text-blue">{{ store.grabHistoryStats.total_collected || 0 }}</div>
        <div class="metric-footer">Mənbələrdən oxunmuş</div>
      </div>

      <div class="metric-card">
        <div class="metric-header">
          <span class="metric-label">DB-yə Yeni Əlavə Edilən</span>
          <div class="metric-icon icon-green">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
          </div>
        </div>
        <div class="metric-value text-green">{{ store.grabHistoryStats.total_added || 0 }}</div>
        <div class="metric-footer">İlkin yeni xəbərlər</div>
      </div>

      <div class="metric-card">
        <div class="metric-header">
          <span class="metric-label">Təkrarlanan (Dublikat)</span>
          <div class="metric-icon icon-yellow">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
            </svg>
          </div>
        </div>
        <div class="metric-value text-yellow">{{ store.grabHistoryStats.total_duplicates || 0 }}</div>
        <div class="metric-footer">Artıq bazada olanlar</div>
      </div>
    </div>

    <!-- Filters & Toolbar -->
    <div class="history-card">
      <div class="history-toolbar">
        <div class="toolbar-left">
          <label class="filter-group">
            <span class="filter-label">Mənbəyə görə filtr:</span>
            <select v-model="selectedSource" @change="onSourceChange" class="select-source">
              <option value="all">Bütün Mənbələr (12)</option>
              <option v-for="s in store.sources" :key="s.id" :value="s.id">
                {{ s.name }}
              </option>
            </select>
          </label>
        </div>

        <div class="toolbar-right">
          <span class="records-count">Ümumi: <strong>{{ store.grabHistoryTotal }}</strong> qeyd</span>
        </div>
      </div>

      <!-- Table Container -->
      <div class="table-responsive">
        <div v-if="store.grabHistoryLoading" class="loading-state">
          <div class="spinner"></div>
          <span>Tarixçə yüklənir...</span>
        </div>

        <div v-else-if="store.grabHistory.length === 0" class="empty-history">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/>
          </svg>
          <p>Hələ heç bir toplanış qeydə alınmayıb.</p>
          <button @click="store.grabAllSources()" :disabled="store.loading" class="btn btn-primary btn-sm">
            İlk Toplanışı Başlat
          </button>
        </div>

        <table v-else class="history-table">
          <thead>
            <tr>
              <th>İcra Vaxtı (Run Time)</th>
              <th>Mənbə</th>
              <th>İcra Müddəti</th>
              <th class="text-center">Toplanan Xəbər</th>
              <th class="text-center">DB-yə Əlavə</th>
              <th class="text-center">Dublikat</th>
              <th>Status</th>
              <th class="text-right">Əməliyyat</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in store.grabHistory" :key="item.id" :class="{ 'row-error': item.status === 'error' }">
              <!-- Run Time -->
              <td class="cell-time">
                <div class="time-primary">{{ formatDateTime(item.run_time) }}</div>
                <div class="time-relative">{{ formatTimeAgo(item.run_time) }}</div>
              </td>

              <!-- Source -->
              <td class="cell-source">
                <div class="source-info">
                  <span class="source-name">{{ item.source_name || item.source_id }}</span>
                  <span class="source-category-badge">{{ item.source_category || 'texnologiya' }}</span>
                </div>
              </td>

              <!-- Duration -->
              <td class="cell-duration">
                <span class="duration-badge">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/>
                  </svg>
                  {{ item.duration_seconds > 0 ? item.duration_seconds + ' san' : '< 0.1 san' }}
                </span>
              </td>

              <!-- Collected -->
              <td class="text-center">
                <span class="badge-count badge-collected" title="Mənbədən çəkilmiş xəbər sayı">
                  {{ item.news_collected }}
                </span>
              </td>

              <!-- Added to DB -->
              <td class="text-center">
                <span v-if="item.news_added > 0" class="badge-count badge-added" title="Bazaya yeni əlavə edilən">
                  +{{ item.news_added }} yeni
                </span>
                <span v-else class="badge-count badge-zero">
                  0
                </span>
              </td>

              <!-- Duplicates -->
              <td class="text-center">
                <span v-if="item.duplicate_count > 0" class="badge-count badge-dup" title="Təkrar olaraq qeydə alınan">
                  {{ item.duplicate_count }}
                </span>
                <span v-else class="badge-count badge-zero">
                  0
                </span>
              </td>

              <!-- Status -->
              <td>
                <span v-if="item.status === 'success'" class="status-tag tag-success">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  Uğurlu
                </span>
                <span 
                  v-else-if="item.status === 'error'" 
                  class="status-tag tag-error tag-clickable" 
                  @click="openErrorModal(item)"
                  title="Xətanın detallarına baxmaq üçün klikləyin"
                >
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                  </svg>
                  <span>Xəta</span>
                  <svg class="icon-info-hint" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                  </svg>
                </span>
                <span v-else class="status-tag tag-skipped">
                  Keçildi
                </span>
              </td>

              <!-- Action: Trigger single grab again -->
              <td class="text-right">
                <button 
                  @click="store.grabSource(item.source_id)" 
                  :disabled="store.grabbingSourceId === item.source_id" 
                  class="btn-icon" 
                  :title="item.source_name + ' mənbəyini indi yoxla'"
                >
                  <svg class="spin-on-load" :class="{ spinning: store.grabbingSourceId === item.source_id }" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="store.grabHistoryTotalPages > 1" class="history-pagination">
        <button 
          @click="changePage(store.grabHistoryPage - 1)" 
          :disabled="store.grabHistoryPage <= 1 || store.grabHistoryLoading"
          class="btn btn-secondary btn-sm"
        >
          ← Əvvəlki
        </button>

        <span class="page-info">
          Səhifə <strong>{{ store.grabHistoryPage }}</strong> / <strong>{{ store.grabHistoryTotalPages }}</strong>
        </span>

        <button 
          @click="changePage(store.grabHistoryPage + 1)" 
          :disabled="store.grabHistoryPage >= store.grabHistoryTotalPages || store.grabHistoryLoading"
          class="btn btn-secondary btn-sm"
        >
          Növbəti →
        </button>
      </div>
    </div>

    <!-- Error Details Modal -->
    <transition name="modal-fade">
      <div v-if="selectedErrorItem" class="modal-backdrop" @click.self="closeErrorModal">
        <div class="modal-dialog modal-dialog-error">
          <div class="modal-header modal-header-danger">
            <div class="modal-title-group">
              <div class="modal-icon-danger">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/>
                  <line x1="12" y1="8" x2="12" y2="12"/>
                  <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
              </div>
              <div>
                <h3 class="modal-title">Toplanış Xətası Detalları</h3>
                <span class="modal-source-badge">{{ selectedErrorItem.source_name || selectedErrorItem.source_id }}</span>
              </div>
            </div>
            <button class="close-btn" @click="closeErrorModal" aria-label="Bağla">&times;</button>
          </div>

          <div class="modal-body">
            <!-- Information Grid -->
            <div class="modal-info-grid">
              <div class="info-row">
                <span class="info-key">İcra Vaxtı:</span>
                <span class="info-val">{{ formatDateTime(selectedErrorItem.run_time) }} ({{ formatTimeAgo(selectedErrorItem.run_time) }})</span>
              </div>
              <div class="info-row">
                <span class="info-key">Mənbə:</span>
                <span class="info-val font-semibold">{{ selectedErrorItem.source_name || selectedErrorItem.source_id }}</span>
              </div>
              <div class="info-row" v-if="getSourceUrl(selectedErrorItem.source_id)">
                <span class="info-key">Hədəf URL:</span>
                <a :href="getSourceUrl(selectedErrorItem.source_id)" target="_blank" rel="noopener noreferrer" class="info-link">
                  <span>{{ getSourceUrl(selectedErrorItem.source_id) }}</span>
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                  </svg>
                </a>
              </div>
              <div class="info-row">
                <span class="info-key">Xəta Tipi:</span>
                <span class="error-badge" :class="getErrorCategoryClass(selectedErrorItem.error_message)">
                  {{ getErrorCategoryLabel(selectedErrorItem.error_message) }}
                </span>
              </div>
            </div>

            <!-- Diagnosis / Recommended action -->
            <div class="diagnosis-card" v-if="getErrorAdvice(selectedErrorItem.error_message)">
              <div class="diagnosis-header">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                <span>Xətanın Səbəbi və Həlli:</span>
              </div>
              <p class="diagnosis-desc">{{ getErrorAdvice(selectedErrorItem.error_message) }}</p>
            </div>

            <!-- Error message code snippet -->
            <div class="code-box-wrap">
              <div class="code-box-header">
                <span class="code-box-title">Sistem Xəta Mesajı (Error Message):</span>
                <button type="button" @click="copyErrorMessage" class="btn-copy">
                  <svg v-if="!copied" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                  </svg>
                  <svg v-else width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <span>{{ copied ? 'Kopyalandı!' : 'Mesajı Kopyala' }}</span>
                </button>
              </div>
              <pre class="error-message-code">{{ selectedErrorItem.error_message || 'Xəta mesajı qeydə alınmayıb.' }}</pre>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeErrorModal">
              Bağla
            </button>
            <button 
              type="button" 
              class="btn btn-primary" 
              :disabled="store.grabbingSourceId === selectedErrorItem.source_id"
              @click="retryFromModal"
            >
              <svg class="spin-on-load" :class="{ spinning: store.grabbingSourceId === selectedErrorItem.source_id }" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
              </svg>
              <span>{{ store.grabbingSourceId === selectedErrorItem.source_id ? 'Toplanır...' : 'İndi Yenidən Yoxla' }}</span>
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAiWriterStore } from '../stores/aiWriter'

export default {
  name: 'GrabHistory',
  setup() {
    const store = useAiWriterStore()
    const route = useRoute()
    const selectedSource = ref(route.query.source || 'all')
    const selectedErrorItem = ref(null)
    const copied = ref(false)

    const handleKeyDown = (e) => {
      if (e.key === 'Escape' && selectedErrorItem.value) {
        closeErrorModal()
      }
    }

    onMounted(async () => {
      window.addEventListener('keydown', handleKeyDown)
      if (!store.sources || store.sources.length === 0) {
        await store.fetchSources()
      }
      await store.fetchGrabHistory(1, selectedSource.value)
    })

    onUnmounted(() => {
      window.removeEventListener('keydown', handleKeyDown)
    })

    const refreshHistory = async () => {
      await store.fetchGrabHistory(store.grabHistoryPage, selectedSource.value)
    }

    const onSourceChange = async () => {
      await store.fetchGrabHistory(1, selectedSource.value)
    }

    const changePage = async (page) => {
      if (page >= 1 && page <= store.grabHistoryTotalPages) {
        await store.fetchGrabHistory(page, selectedSource.value)
      }
    }

    const openErrorModal = (item) => {
      selectedErrorItem.value = item
      copied.value = false
    }

    const closeErrorModal = () => {
      selectedErrorItem.value = null
      copied.value = false
    }

    const getSourceUrl = (sourceId) => {
      const src = store.sources?.find(s => s.id === sourceId)
      return src?.url || ''
    }

    const getErrorCategoryLabel = (msg) => {
      if (!msg) return 'Naməlum Xəta'
      if (msg.includes('Cloudflare')) return 'Cloudflare Bot Mühafizəsi'
      if (msg.includes('403') || msg.includes('401')) return 'Giriş Qadağandır (HTTP 401/403)'
      if (msg.includes('404')) return 'Səhifə Tapılmadı (HTTP 404)'
      if (msg.includes('heç bir xəbər tapılmadı') || msg.includes('0 xəbər')) return 'Boş Cavab (0 Xəbər)'
      if (msg.includes('Bağlantı xətası') || msg.includes('cURL')) return 'Şəbəkə / Bağlantı Kəsilməsi'
      return 'Sistem / Sorğu Xətası'
    }

    const getErrorCategoryClass = (msg) => {
      if (!msg) return 'badge-cat-other'
      if (msg.includes('Cloudflare')) return 'badge-cat-cf'
      if (msg.includes('heç bir xəbər tapılmadı') || msg.includes('0 xəbər')) return 'badge-cat-empty'
      return 'badge-cat-http'
    }

    const getErrorAdvice = (msg) => {
      if (!msg) return null
      if (msg.includes('Cloudflare')) {
        return 'Mənbə saytı Cloudflare avtomatlaşdırılmış bot mühafizəsi (Turnstile / Challenge) tətbiq edir. Brauzerdə mənbə linkini yoxlayın və ya Tənzimləmələr bölməsindən FlareSolverr / proxy inteqrasiyası quraşdırın.'
      }
      if (msg.includes('heç bir xəbər tapılmadı') || msg.includes('0 xəbər')) {
        return 'Server 200 OK statusu ilə cavab verdi, lakin mənbə saytının xəbər kartları strukturu (HTML selector) dəyişmiş ola bilər və ya bu bölmədə cari xəbər yoxdur.'
      }
      if (msg.includes('404')) {
        return 'Bu xəbər bölməsinin URL ünvanı dəyişdirilmiş və ya ləğv edilmiş ola bilər. Mənbələr bölməsindən URL-i yoxlayın.'
      }
      if (msg.includes('Bağlantı xətası') || msg.includes('cURL')) {
        return 'Mənbə serveri ilə bağlantı qurularkən vaxt bitdi (timeout) və ya şəbəkə xətası baş verdi. Bir neçə dəqiqə sonra təkrar yoxlayın.'
      }
      return null
    }

    const copyErrorMessage = async () => {
      if (selectedErrorItem.value?.error_message) {
        try {
          await navigator.clipboard.writeText(selectedErrorItem.value.error_message)
          copied.value = true
          setTimeout(() => { copied.value = false }, 2000)
        } catch (e) {
          // fallback
          copied.value = true
          setTimeout(() => { copied.value = false }, 2000)
        }
      }
    }

    const retryFromModal = async () => {
      if (!selectedErrorItem.value) return
      const sid = selectedErrorItem.value.source_id
      await store.grabSource(sid)
      const latest = store.grabHistory.find(h => h.source_id === sid)
      if (latest) {
        if (latest.status === 'success') {
          closeErrorModal()
        } else {
          selectedErrorItem.value = latest
        }
      }
    }

    const formatDateTime = (dateStr) => {
      if (!dateStr) return '—'
      const d = new Date(dateStr)
      return d.toLocaleString('az-AZ', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      })
    }

    const formatTimeAgo = (dateStr) => {
      if (!dateStr) return ''
      const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000)
      if (diff < 60) return 'bayaq'
      if (diff < 3600) return `${Math.floor(diff / 60)} dəq əvvəl`
      if (diff < 86400) return `${Math.floor(diff / 3600)} saat əvvəl`
      return `${Math.floor(diff / 86400)} gün əvvəl`
    }

    return {
      store,
      selectedSource,
      selectedErrorItem,
      copied,
      openErrorModal,
      closeErrorModal,
      getSourceUrl,
      getErrorCategoryLabel,
      getErrorCategoryClass,
      getErrorAdvice,
      copyErrorMessage,
      retryFromModal,
      refreshHistory,
      onSourceChange,
      changePage,
      formatDateTime,
      formatTimeAgo
    }
  }
}
</script>

<style scoped>
.grab-history-page {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 16px;
}

.page-header h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-text-primary, #fff);
  margin: 0 0 6px 0;
}

.page-desc {
  font-size: 0.9rem;
  color: var(--color-text-muted, #94a3b8);
  margin: 0;
}

.page-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

/* Metrics Grid */
.metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
}

.metric-card {
  background: var(--color-surface, #131b2e);
  border: 1px solid var(--color-border, #1e293b);
  border-radius: 12px;
  padding: 18px 20px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  transition: transform 0.2s, border-color 0.2s;
}

.metric-card:hover {
  border-color: rgba(0, 240, 255, 0.3);
  transform: translateY(-2px);
}

.metric-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.metric-label {
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--color-text-muted, #94a3b8);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.metric-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-cyan { background: rgba(0, 240, 255, 0.12); color: #00f0ff; }
.icon-blue { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
.icon-green { background: rgba(16, 185, 129, 0.12); color: #10b981; }
.icon-yellow { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }

.metric-value {
  font-size: 1.8rem;
  font-weight: 800;
  color: var(--color-text-primary, #fff);
  line-height: 1.1;
}

.text-blue { color: #38bdf8 !important; }
.text-green { color: #34d399 !important; }
.text-yellow { color: #fbbf24 !important; }

.metric-footer {
  font-size: 0.78rem;
  color: var(--color-text-muted, #64748b);
}

/* History Card & Table */
.history-card {
  background: var(--color-surface, #131b2e);
  border: 1px solid var(--color-border, #1e293b);
  border-radius: 12px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.history-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid var(--color-border, #1e293b);
  flex-wrap: wrap;
  gap: 12px;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.88rem;
  color: var(--color-text-secondary, #cbd5e1);
}

.select-source {
  background: var(--color-bg, #0b0f19);
  border: 1px solid var(--color-border, #1e293b);
  color: var(--color-text-primary, #fff);
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 0.88rem;
  outline: none;
  cursor: pointer;
}

.records-count {
  font-size: 0.85rem;
  color: var(--color-text-muted, #94a3b8);
}

.records-count strong {
  color: var(--color-text-primary, #fff);
}

.table-responsive {
  overflow-x: auto;
  min-height: 250px;
}

.history-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.88rem;
}

.history-table th {
  background: rgba(15, 23, 42, 0.6);
  padding: 12px 18px;
  font-weight: 600;
  color: var(--color-text-muted, #94a3b8);
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1px solid var(--color-border, #1e293b);
}

.history-table td {
  padding: 14px 18px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  color: var(--color-text-secondary, #cbd5e1);
  vertical-align: middle;
}

.history-table tbody tr:hover {
  background: rgba(255, 255, 255, 0.02);
}

.row-error {
  background: rgba(239, 68, 68, 0.04) !important;
}

.cell-time {
  white-space: nowrap;
}

.time-primary {
  font-weight: 600;
  color: var(--color-text-primary, #fff);
}

.time-relative {
  font-size: 0.75rem;
  color: var(--color-text-muted, #64748b);
  margin-top: 2px;
}

.cell-source {
  font-weight: 500;
}

.source-info {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.source-name {
  color: var(--color-text-primary, #fff);
  font-weight: 600;
}

.source-category-badge {
  font-size: 0.72rem;
  padding: 2px 8px;
  background: rgba(255, 255, 255, 0.06);
  border-radius: 4px;
  color: var(--color-text-muted, #94a3b8);
}

.duration-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.8rem;
  color: var(--color-text-muted, #94a3b8);
  background: rgba(255, 255, 255, 0.04);
  padding: 3px 8px;
  border-radius: 4px;
}

/* Count Badges */
.badge-count {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 6px;
  font-weight: 700;
  font-size: 0.85rem;
}

.badge-collected {
  background: rgba(59, 130, 246, 0.15);
  color: #38bdf8;
  border: 1px solid rgba(59, 130, 246, 0.25);
}

.badge-added {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.badge-dup {
  background: rgba(245, 158, 11, 0.15);
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.25);
}

.badge-zero {
  color: var(--color-text-muted, #64748b);
  background: transparent;
}

/* Status Tags */
.status-tag {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 0.78rem;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 20px;
}

.tag-success {
  background: rgba(16, 185, 129, 0.12);
  color: #10b981;
}

.tag-error {
  background: rgba(239, 68, 68, 0.12);
  color: #ef4444;
  cursor: help;
}

.tag-error.tag-clickable {
  cursor: pointer;
  border: 1px solid rgba(239, 68, 68, 0.3);
  transition: all 0.2s ease;
  user-select: none;
}

.tag-error.tag-clickable:hover {
  background: rgba(239, 68, 68, 0.22);
  color: #f87171;
  border-color: rgba(239, 68, 68, 0.6);
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.25);
}

.icon-info-hint {
  opacity: 0.85;
  margin-left: 2px;
}

/* Modal */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(5px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal-dialog-error {
  width: 100%;
  max-width: 620px;
  background: var(--color-surface, #131b2e);
  border: 1px solid rgba(239, 68, 68, 0.3);
  border-radius: 16px;
  box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 30px rgba(239, 68, 68, 0.15);
  overflow: hidden;
  animation: modalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modalPop {
  from { transform: scale(0.95); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

.modal-header-danger {
  padding: 18px 24px;
  background: linear-gradient(180deg, rgba(239, 68, 68, 0.15) 0%, rgba(239, 68, 68, 0.04) 100%);
  border-bottom: 1px solid rgba(239, 68, 68, 0.2);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.modal-title-group {
  display: flex;
  align-items: center;
  gap: 14px;
}

.modal-icon-danger {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  background: rgba(239, 68, 68, 0.2);
  border: 1px solid rgba(239, 68, 68, 0.4);
  color: #f87171;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.modal-title {
  font-size: 1.15rem;
  font-weight: 700;
  color: #fff;
  margin: 0 0 3px 0;
}

.modal-source-badge {
  font-size: 0.8rem;
  color: var(--color-text-muted, #94a3b8);
  font-weight: 500;
}

.close-btn {
  background: transparent;
  border: none;
  color: var(--color-text-muted, #94a3b8);
  font-size: 26px;
  line-height: 1;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  transition: color 0.15s;
}

.close-btn:hover {
  color: #fff;
}

.modal-body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.modal-info-grid {
  display: flex;
  flex-direction: column;
  gap: 10px;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--color-border, #1e293b);
  border-radius: 10px;
  padding: 14px 16px;
}

.info-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  font-size: 0.86rem;
}

.info-key {
  color: var(--color-text-muted, #94a3b8);
  font-weight: 500;
  flex-shrink: 0;
}

.info-val {
  color: var(--color-text-primary, #fff);
  text-align: right;
  word-break: break-all;
}

.info-link {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  color: #38bdf8;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.15s;
  word-break: break-all;
}

.info-link:hover {
  color: #7dd3fc;
  text-decoration: underline;
}

.error-badge {
  display: inline-block;
  font-size: 0.76rem;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 6px;
}

.badge-cat-cf {
  background: rgba(249, 115, 22, 0.15);
  color: #fb923c;
  border: 1px solid rgba(249, 115, 22, 0.35);
}

.badge-cat-empty {
  background: rgba(234, 179, 8, 0.15);
  color: #facc15;
  border: 1px solid rgba(234, 179, 8, 0.35);
}

.badge-cat-http {
  background: rgba(239, 68, 68, 0.15);
  color: #f87171;
  border: 1px solid rgba(239, 68, 68, 0.35);
}

.badge-cat-other {
  background: rgba(148, 163, 184, 0.15);
  color: #cbd5e1;
  border: 1px solid rgba(148, 163, 184, 0.3);
}

.diagnosis-card {
  background: rgba(0, 240, 255, 0.04);
  border: 1px solid rgba(0, 240, 255, 0.2);
  border-radius: 10px;
  padding: 14px 16px;
}

.diagnosis-header {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #00f0ff;
  font-size: 0.85rem;
  font-weight: 700;
  margin-bottom: 6px;
}

.diagnosis-desc {
  font-size: 0.84rem;
  color: var(--color-text-secondary, #cbd5e1);
  line-height: 1.5;
  margin: 0;
}

.code-box-wrap {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.code-box-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.code-box-title {
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--color-text-muted, #94a3b8);
}

.btn-copy {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid var(--color-border, #1e293b);
  color: var(--color-text-secondary, #cbd5e1);
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.78rem;
  cursor: pointer;
  transition: all 0.15s;
}

.btn-copy:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #fff;
}

.error-message-code {
  margin: 0;
  padding: 14px 16px;
  background: rgba(15, 23, 42, 0.9);
  border: 1px solid rgba(239, 68, 68, 0.25);
  border-radius: 8px;
  color: #f87171;
  font-family: 'JetBrains Mono', monospace, Consolas, Courier;
  font-size: 0.84rem;
  line-height: 1.5;
  white-space: pre-wrap;
  word-break: break-word;
  max-height: 180px;
  overflow-y: auto;
}

.modal-footer {
  padding: 16px 24px;
  background: rgba(15, 23, 42, 0.4);
  border-top: 1px solid var(--color-border, #1e293b);
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
}

.tag-skipped {
  background: rgba(148, 163, 184, 0.12);
  color: #94a3b8;
}

.btn-icon {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--color-border, #1e293b);
  color: var(--color-text-secondary, #cbd5e1);
  width: 32px;
  height: 32px;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
}

.btn-icon:hover {
  background: rgba(0, 240, 255, 0.15);
  color: #00f0ff;
  border-color: rgba(0, 240, 255, 0.3);
}

/* States */
.loading-state, .empty-history {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 60px 20px;
  color: var(--color-text-muted, #94a3b8);
}

.spinner {
  width: 28px;
  height: 28px;
  border: 3px solid rgba(255, 255, 255, 0.1);
  border-top-color: #00f0ff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.spinning {
  animation: spin 0.8s linear infinite;
}

/* Pagination */
.history-pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-top: 1px solid var(--color-border, #1e293b);
}

.page-info {
  font-size: 0.88rem;
  color: var(--color-text-muted, #94a3b8);
}

.page-info strong {
  color: var(--color-text-primary, #fff);
}

.text-center { text-align: center; }
.text-right { text-align: right; }
</style>
