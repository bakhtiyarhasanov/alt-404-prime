<template>
  <div class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-container">
      <!-- Modal Header -->
      <div class="modal-header">
        <div class="header-titles">
          <div class="header-badges">
            <span :class="'badge-' + article.status">{{ formatStatus(article.status) }}</span>
            <span class="badge-source">{{ article.source_name }}</span>
            <span v-if="article.is_duplicate" class="badge-dup-flag">Təkrar Xəbər</span>
          </div>
          <h2>{{ article.rewritten_title || article.source_title }}</h2>
        </div>
        <button class="btn-close" @click="$emit('close')">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>

      <!-- Modal Body (Side by Side or Tabbed) -->
      <div class="modal-body">
        <!-- Status Message Banner if error or duplicate -->
        <div v-if="article.status_message" class="info-banner" :class="article.status === 'error' ? 'banner-error' : 'banner-warning'">
          <strong>Qeyd:</strong> {{ article.status_message }}
        </div>

        <div class="comparison-grid">
          <!-- Left: Original Source -->
          <div class="panel panel-original">
            <div class="panel-header">
              <span class="panel-tag">Orijinal Mənbə</span>
              <a :href="article.source_url" target="_blank" rel="noopener noreferrer" class="link-source">
                Saytda Bax
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                </svg>
              </a>
            </div>

            <div v-if="article.source_image_url" class="panel-img-wrap">
              <img :src="article.source_image_url" alt="Source image" class="panel-img" />
            </div>

            <h3 class="panel-title">{{ article.source_title }}</h3>

            <div v-if="article.source_excerpt" class="panel-excerpt">
              {{ article.source_excerpt }}
            </div>

            <div class="panel-content-scroll" v-html="article.source_content || '<p class=text-dim>Mətn mövcud deyil</p>'"></div>
          </div>

          <!-- Right: Rewritten / Draft Azerbaijani -->
          <div class="panel panel-rewritten">
            <div class="panel-header">
              <div style="display:flex;align-items:center;gap:8px;">
                <span class="panel-tag tag-az">Azərbaycan Dilində Qaralama</span>
                <span v-if="article.posted_article_id" class="badge-posted-web">Veb Draft İD: {{ article.posted_article_id.slice(0, 8) }}</span>
              </div>
              <button 
                v-if="article.rewritten_title"
                @click="regenerateCurrent" 
                :disabled="isProcessing" 
                class="btn-mini-action"
                title="OpenAI ilə yenidən yaz"
              >
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="23 4 23 10 17 10"/>
                  <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                </svg>
                <span>Yenidən Yaz</span>
              </button>
            </div>

            <div v-if="article.rewritten_title">
              <h3 class="panel-title text-gold">{{ article.rewritten_title }}</h3>
              
              <div v-if="article.rewritten_excerpt" class="panel-excerpt excerpt-gold">
                {{ article.rewritten_excerpt }}
              </div>

              <!-- Tags -->
              <div v-if="parsedTags.length" class="tags-row">
                <span v-for="tag in parsedTags" :key="tag" class="tag-pill">{{ tag }}</span>
              </div>

              <div class="panel-content-scroll rewritten-html" v-html="article.rewritten_content"></div>
            </div>

            <div v-else class="not-rewritten-placeholder">
              <div class="placeholder-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M12 2a4 4 0 0 1 4 4v2a4 4 0 0 1-8 0V6a4 4 0 0 1 4-4z"/>
                  <rect x="3" y="10" width="18" height="12" rx="4"/>
                </svg>
              </div>
              <h4>Hələ Generasiya Edilməyib</h4>
              <p>Bu xəbər üçün OpenAI adaptasiyası başladılmayıb. Aşağıdakı düyməyə klikləyərək xəbəri dərhal Azərbaycan dilində yenidən yazın və alt404-ə qaralama (draft) olaraq göndərin.</p>
              <button 
                @click="processCurrent" 
                :disabled="isProcessing" 
                class="btn btn-primary"
              >
                <span v-if="isProcessing">Generasiya edilir və Paylaşılır...</span>
                <span v-else>İndi Generasiya Et və Qaralama Kimi Əlavə Et</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer">
        <div class="footer-left">
          <button 
            @click="toggleDuplicate" 
            class="btn btn-secondary btn-sm"
          >
            {{ article.is_duplicate ? 'Təkrarı İqnor Et (Aktivləşdir)' : 'Təkrar (Dublikat) Kimi İşarələ' }}
          </button>
          <button 
            v-if="article.status === 'error'" 
            @click="retryCurrent" 
            class="btn btn-secondary btn-sm"
          >
            Statusu Sıfırla
          </button>
        </div>

        <div class="footer-right">
          <button @click="$emit('close')" class="btn btn-secondary btn-sm">Bağla</button>
          <button 
            v-if="article.status !== 'posted'" 
            @click="processCurrent" 
            :disabled="isProcessing" 
            class="btn btn-primary btn-sm"
          >
            <span v-if="isProcessing">Yazılır...</span>
            <span v-else>Qaralama Et</span>
          </button>
          <button 
            v-else
            @click="regenerateCurrent" 
            :disabled="isProcessing" 
            class="btn btn-warning btn-sm"
            title="OpenAI ilə yenidən yaz"
          >
            <span v-if="isProcessing">Yenidən yazılır...</span>
            <span v-else>
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:-1px;margin-right:4px;">
                <polyline points="23 4 23 10 17 10"/>
                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
              </svg>
              Yenidən Yaz (Regenerate)
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useAiWriterStore } from '../stores/aiWriter'

export default {
  name: 'ArticleModal',
  props: {
    article: {
      type: Object,
      required: true
    }
  },
  emits: ['close'],
  setup(props) {
    const store = useAiWriterStore()
    const isProcessing = ref(false)

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

    const parsedTags = computed(() => {
      const t = props.article.rewritten_tags
      if (!t) return []
      if (Array.isArray(t)) return t
      try { return JSON.parse(t) } catch (e) { return [t] }
    })

    const processCurrent = async () => {
      isProcessing.value = true
      try {
        const res = await store.processNewsItem(props.article.id)
        const updated = res?.item || res?.result?.item
        if (updated) {
          Object.assign(props.article, updated)
        } else if (res?.result?.rewritten) {
          props.article.status = 'posted'
          props.article.rewritten_title = res.result.rewritten.title
          props.article.rewritten_excerpt = res.result.rewritten.excerpt
          props.article.rewritten_content = res.result.rewritten.content
          props.article.rewritten_tags = res.result.rewritten.tags
          props.article.posted_article_id = res.result.article_id
        }
      } catch (e) {
        // Error toast shown by store
      } finally {
        isProcessing.value = false
      }
    }

    const toggleDuplicate = async () => {
      await store.toggleDuplicate(props.article.id)
      props.article.is_duplicate = !props.article.is_duplicate
      props.article.status = props.article.is_duplicate ? 'duplicate' : 'new'
    }

    const retryCurrent = async () => {
      await store.retryNews(props.article.id)
      props.article.status = 'new'
    }

    const regenerateCurrent = async () => {
      isProcessing.value = true
      try {
        const res = await store.regenerateNewsItem(props.article.id)
        const updated = res?.item || res?.result?.item
        if (updated) {
          Object.assign(props.article, updated)
        } else if (res?.result?.rewritten) {
          props.article.status = 'posted'
          props.article.rewritten_title = res.result.rewritten.title
          props.article.rewritten_excerpt = res.result.rewritten.excerpt
          props.article.rewritten_content = res.result.rewritten.content
          props.article.rewritten_tags = res.result.rewritten.tags
          props.article.posted_article_id = res.result.article_id
        }
      } catch (e) {
        // Error toast shown by store
      } finally {
        isProcessing.value = false
      }
    }

    return {
      store,
      isProcessing,
      formatStatus,
      parsedTags,
      processCurrent,
      regenerateCurrent,
      toggleDuplicate,
      retryCurrent
    }
  }
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(8px);
  z-index: 200;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}

.modal-container {
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-lg);
  width: 100%;
  max-width: 1100px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8);
  overflow: hidden;
}

/* Header */
.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid var(--border-subtle);
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  background: var(--bg-card);
}

.header-badges {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

.badge-source {
  background: rgba(252, 219, 86, 0.15);
  color: var(--color-primary);
  border: 1px solid rgba(252, 219, 86, 0.3);
  font-size: 0.75rem;
  padding: 2px 8px;
  border-radius: 4px;
  font-weight: 600;
}

.badge-dup-flag {
  background: rgba(245, 158, 11, 0.15);
  color: var(--status-duplicate);
  font-size: 0.75rem;
  padding: 2px 8px;
  border-radius: 4px;
  font-weight: 600;
}

.header-titles h2 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #fff;
  line-height: 1.35;
}

.btn-close {
  background: transparent;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: 6px;
  border-radius: 6px;
}
.btn-close:hover {
  color: #fff;
  background: rgba(255, 255, 255, 0.08);
}

/* Body */
.modal-body {
  padding: 24px;
  overflow-y: auto;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.info-banner {
  padding: 12px 16px;
  border-radius: var(--radius-sm);
  font-size: 0.85rem;
}
.banner-warning {
  background: rgba(245, 158, 11, 0.1);
  border: 1px solid rgba(245, 158, 11, 0.3);
  color: var(--status-duplicate);
}
.banner-error {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: var(--status-error);
}

.comparison-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.panel {
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-md);
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.panel-tag {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--text-dim);
}

.tag-az {
  color: var(--color-primary);
}

.badge-posted-web {
  font-size: 0.72rem;
  background: rgba(16, 185, 129, 0.15);
  color: var(--status-posted);
  padding: 2px 6px;
  border-radius: 4px;
}

.link-source {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: var(--color-accent);
  font-size: 0.78rem;
  text-decoration: none;
}

.panel-img-wrap {
  width: 100%;
  height: 180px;
  border-radius: var(--radius-sm);
  overflow: hidden;
}

.panel-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.panel-title {
  font-size: 1.05rem;
  font-weight: 700;
  line-height: 1.4;
  color: #fff;
}

.text-gold {
  color: var(--color-primary);
}

.panel-excerpt {
  font-size: 0.88rem;
  color: var(--text-muted);
  background: rgba(0, 0, 0, 0.2);
  padding: 10px;
  border-radius: 6px;
  border-left: 3px solid #3b82f6;
  font-style: italic;
}

.excerpt-gold {
  border-left-color: var(--color-primary);
}

.tags-row {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.tag-pill {
  font-size: 0.72rem;
  background: rgba(255, 255, 255, 0.06);
  color: #fff;
  padding: 3px 8px;
  border-radius: 99px;
}

.panel-content-scroll {
  max-height: 380px;
  overflow-y: auto;
  font-size: 0.88rem;
  line-height: 1.6;
  color: var(--text-muted);
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.rewritten-html {
  color: #e2e8f0;
}

.not-rewritten-placeholder {
  text-align: center;
  padding: 40px 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  margin: auto;
}

.placeholder-icon {
  color: var(--text-dim);
}

.not-rewritten-placeholder h4 {
  font-size: 1.1rem;
  font-weight: 700;
  color: #fff;
}

.not-rewritten-placeholder p {
  font-size: 0.85rem;
  color: var(--text-muted);
  max-width: 360px;
}

/* Footer */
.modal-footer {
  padding: 16px 24px;
  background: var(--bg-card);
  border-top: 1px solid var(--border-subtle);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.footer-left, .footer-right {
  display: flex;
  align-items: center;
  gap: 10px;
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

.btn-mini-action {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: rgba(245, 158, 11, 0.12);
  border: 1px solid rgba(245, 158, 11, 0.3);
  color: #fbbf24;
  padding: 3px 8px;
  border-radius: var(--radius-sm);
  font-size: 0.72rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-mini-action:hover:not(:disabled) {
  background: rgba(245, 158, 11, 0.22);
  border-color: #fbbf24;
}

@media (max-width: 850px) {
  .comparison-grid {
    grid-template-columns: 1fr;
  }
}
</style>
