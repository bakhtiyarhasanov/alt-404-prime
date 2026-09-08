<template>
  <div class="sources-page">
    <div class="page-header">
      <div>
        <h1>Xəbər Mənbələri (12 Sayt)</h1>
        <p class="page-desc">Mənbələri aktiv/deaktiv edin, AI rewrite və yenilənmə (retry) intervallarını tənzimləyin.</p>
      </div>
      <div class="page-actions">
        <button @click="store.grabAllSources()" :disabled="store.loading" class="btn btn-primary">
          <svg class="spin-on-load" :class="{ spinning: store.loading }" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
          </svg>
          <span>Hamısını İndi Topla</span>
        </button>
      </div>
    </div>

    <!-- Sources Grid -->
    <div class="sources-grid">
      <div v-for="src in store.sources" :key="src.id" class="source-card" :class="{ 'card-disabled': !src.is_enabled }">
        <!-- Card Top -->
        <div class="card-top">
          <div class="source-title-row">
            <span class="status-indicator" :class="src.is_enabled ? 'status-online' : 'status-offline'"></span>
            <div class="source-names">
              <h3>{{ src.name }}</h3>
              <a :href="src.url" target="_blank" class="source-url" rel="noopener noreferrer">
                {{ formatDomain(src.url) }}
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                </svg>
              </a>
            </div>
          </div>

          <div class="source-top-badges">
            <span v-if="!src.rewrite_enabled" class="badge-gov">Rəsmi / Birbaşa Kopyalanır</span>
            <span v-else class="badge-ai">OpenAI Rewrite</span>
          </div>
        </div>

        <!-- Card Stats -->
        <div class="card-stats-row">
          <div class="stat-box">
            <span class="stat-label">Toplam Xəbər</span>
            <span class="stat-val">{{ src.total_news || 0 }}</span>
          </div>
          <div class="stat-box">
            <span class="stat-label">Paylaşılan (Draft)</span>
            <span class="stat-val stat-green">{{ src.posted_news || 0 }}</span>
          </div>
          <div class="stat-box">
            <span class="stat-label">Son Yoxlanış</span>
            <span class="stat-val">{{ formatDate(src.last_grabbed_at) || 'Heç vaxt' }}</span>
          </div>
        </div>

        <!-- Controls Section -->
        <div class="card-controls">
          <!-- Enable / Disable Switch -->
          <div class="control-toggle">
            <label class="toggle-label">
              <input type="checkbox" :checked="src.is_enabled" @change="toggleEnabled(src, $event.target.checked)" />
              <span class="toggle-slider"></span>
              <span class="toggle-text">{{ src.is_enabled ? 'Aktiv Mənbə' : 'Deaktiv edilib' }}</span>
            </label>
          </div>

          <!-- Rewrite Enable / Disable Switch -->
          <div class="control-toggle">
            <label class="toggle-label">
              <input type="checkbox" :checked="src.rewrite_enabled" @change="toggleRewrite(src, $event.target.checked)" />
              <span class="toggle-slider slider-cyan"></span>
              <span class="toggle-text">{{ src.rewrite_enabled ? 'AI ilə Yenidən Yazılsın' : 'Dəyişikliksiz Kopyalansın' }}</span>
            </label>
          </div>

          <!-- Retry Interval (Minutes) -->
          <div class="control-interval">
            <span class="interval-label">Yenilənmə İntervalı:</span>
            <div class="interval-input-wrap">
              <input 
                type="number" 
                min="5" 
                max="1440" 
                step="5" 
                :value="src.retry_interval_minutes" 
                @blur="updateRetryMinutes(src, $event.target.value)"
                class="input-minutes"
              />
              <span class="unit-min">dəq</span>
            </div>
          </div>

          <!-- Category Selector -->
          <div class="control-category">
            <span class="category-label">Kateqoriya:</span>
            <select :value="src.category" @change="updateCategory(src, $event.target.value)" class="select-category">
              <option value="texnologiya">Texnologiya</option>
              <option value="suni-intellekt">Süni İntellekt</option>
              <option value="startap">Startap</option>
              <option value="avtomobil">Avtomobil</option>
              <option value="elm-gundem">Elm</option>
            </select>
          </div>
        </div>

        <!-- Card Footer Actions -->
        <div class="card-footer">
          <div v-if="src.last_error" class="error-pill" :title="src.last_error">
            Xəta: {{ src.last_error.slice(0, 30) }}...
          </div>
          <div v-else class="status-pill" :class="'pill-' + src.last_status">
            {{ src.last_status === 'running' ? 'İşləyir...' : 'Hazır' }}
          </div>

          <button 
            @click="store.grabSource(src.id)" 
            :disabled="store.grabbingSourceId === src.id"
            class="btn btn-secondary btn-sm"
          >
            <svg class="spin-on-load" :class="{ spinning: store.grabbingSourceId === src.id }" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
            </svg>
            <span>{{ store.grabbingSourceId === src.id ? 'Toplanır...' : 'İndi Yoxla' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted } from 'vue'
import { useAiWriterStore } from '../stores/aiWriter'

export default {
  name: 'Sources',
  setup() {
    const store = useAiWriterStore()

    onMounted(() => {
      store.fetchSources()
    })

    const formatDomain = (url) => {
      try {
        return new URL(url).hostname.replace('www.', '')
      } catch (e) {
        return url
      }
    }

    const formatDate = (d) => {
      if (!d) return null
      return new Date(d).toLocaleString('az-AZ', {
        month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
      })
    }

    const toggleEnabled = (src, val) => {
      store.updateSource(src.id, { is_enabled: val ? 1 : 0 })
    }

    const toggleRewrite = (src, val) => {
      store.updateSource(src.id, { rewrite_enabled: val ? 1 : 0 })
    }

    const updateRetryMinutes = (src, val) => {
      const mins = parseInt(val, 10)
      if (!isNaN(mins) && mins !== src.retry_interval_minutes) {
        store.updateSource(src.id, { retry_interval_minutes: mins })
      }
    }

    const updateCategory = (src, cat) => {
      store.updateSource(src.id, { category: cat })
    }

    return {
      store,
      formatDomain,
      formatDate,
      toggleEnabled,
      toggleRewrite,
      updateRetryMinutes,
      updateCategory
    }
  }
}
</script>

<style scoped>
.sources-page {
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
  color: var(--text-main);
}

.page-desc {
  font-size: 0.9rem;
  color: var(--text-muted);
  margin-top: 4px;
}

/* Grid */
.sources-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 20px;
}

.source-card {
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-lg);
  padding: 22px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  transition: all 0.25s ease;
  box-shadow: var(--shadow-sm);
}

.source-card:hover {
  border-color: var(--border-glow);
  background: var(--bg-card-hover);
}

.card-disabled {
  opacity: 0.6;
  border-style: dashed;
}

/* Top */
.card-top {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.source-title-row {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.status-indicator {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  margin-top: 6px;
  flex-shrink: 0;
}

.status-online { background: #10b981; box-shadow: 0 0 10px #10b981; }
.status-offline { background: #64748b; }

.source-names h3 {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--text-main);
  line-height: 1.3;
}

.source-url {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: var(--color-primary);
  font-size: 0.8rem;
  text-decoration: none;
  margin-top: 3px;
}
.source-url:hover {
  text-decoration: underline;
}

.source-top-badges {
  display: flex;
  gap: 8px;
}

.badge-gov {
  font-size: 0.72rem;
  background: rgba(16, 185, 129, 0.15);
  color: #10b981;
  border: 1px solid rgba(16, 185, 129, 0.3);
  padding: 3px 8px;
  border-radius: 4px;
  font-weight: 600;
}

.badge-ai {
  font-size: 0.72rem;
  background: rgba(0, 240, 255, 0.15);
  color: var(--color-accent);
  border: 1px solid rgba(0, 240, 255, 0.3);
  padding: 3px 8px;
  border-radius: 4px;
  font-weight: 600;
}

/* Stats Row */
.card-stats-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  background: var(--bg-surface);
  padding: 12px;
  border-radius: var(--radius-sm);
  border: 1px solid rgba(255, 255, 255, 0.04);
}

.stat-box {
  display: flex;
  flex-direction: column;
}

.stat-label {
  font-size: 0.7rem;
  color: var(--text-dim);
}

.stat-val {
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--text-main);
  margin-top: 2px;
}

.stat-green { color: #10b981; }

/* Controls */
.card-controls {
  display: flex;
  flex-direction: column;
  gap: 12px;
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  padding: 14px;
  border-radius: var(--radius-sm);
}

.control-toggle {
  display: flex;
  align-items: center;
}

.toggle-label {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  font-size: 0.82rem;
  color: var(--text-main);
  user-select: none;
}

.toggle-label input {
  display: none;
}

.toggle-slider {
  width: 36px;
  height: 20px;
  background: #cbd5e1;
  border-radius: 99px;
  position: relative;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.theme-dark .toggle-slider {
  background: #334155;
}

.toggle-slider::after {
  content: '';
  position: absolute;
  top: 2px;
  left: 2px;
  width: 16px;
  height: 16px;
  background: #fff;
  border-radius: 50%;
  transition: all 0.2s ease;
}

.toggle-label input:checked + .toggle-slider {
  background: #10b981;
}

.toggle-label input:checked + .slider-cyan {
  background: var(--color-accent);
}

.toggle-label input:checked + .toggle-slider::after {
  transform: translateX(16px);
}

.control-interval {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.interval-label {
  font-size: 0.82rem;
  color: var(--text-muted);
}

.interval-input-wrap {
  display: flex;
  align-items: center;
  gap: 6px;
}

.input-minutes {
  width: 60px;
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  color: var(--text-main);
  padding: 5px 8px;
  border-radius: 6px;
  font-size: 0.85rem;
  text-align: center;
}

.input-minutes:focus {
  outline: none;
  border-color: var(--color-accent);
}

.unit-min {
  font-size: 0.8rem;
  color: var(--text-dim);
}

.control-category {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.category-label {
  font-size: 0.82rem;
  color: var(--text-muted);
}

.select-category {
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  color: var(--text-main);
  padding: 5px 10px;
  border-radius: 6px;
  font-size: 0.82rem;
}

/* Card Footer */
.card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: auto;
  padding-top: 10px;
  border-top: 1px solid var(--border-subtle);
}

.status-pill {
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--text-muted);
}
.pill-running { color: var(--color-accent); }

.error-pill {
  font-size: 0.72rem;
  color: #ef4444;
  background: rgba(239, 68, 68, 0.1);
  padding: 2px 6px;
  border-radius: 4px;
}
</style>
