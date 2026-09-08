import { defineStore } from 'pinia'
import api from '../api'

export const useAiWriterStore = defineStore('aiWriter', {
  state: () => ({
    stats: {
      total: 0,
      new: 0,
      posted: 0,
      duplicate: 0,
      error: 0,
      generating: 0,
      sources_total: 12,
      sources_active: 12,
      sources_rewrite: 7
    },
    sources: [],
    news: [],
    totalNews: 0,
    currentPage: 1,
    totalPages: 1,
    newsLimit: 15,
    filters: {
      status: 'all',
      source_id: 'all',
      search: ''
    },
    settings: {},
    loading: false,
    processingId: null,
    grabbingSourceId: null,
    activeArticle: null,
    toast: null
  }),

  actions: {
    showToast(message, type = 'success') {
      this.toast = { message, type, id: Date.now() }
      setTimeout(() => {
        if (this.toast && this.toast.id) {
          this.toast = null
        }
      }, 4000)
    },

    async fetchStats() {
      try {
        const res = await api.get('?endpoint=stats')
        if (res.data.success) {
          this.stats = res.data.stats
        }
      } catch (e) {
        console.warn('Could not fetch stats:', e)
      }
    },

    async fetchSources() {
      this.loading = true
      try {
        const res = await api.get('?endpoint=sources')
        if (res.data.success) {
          this.sources = res.data.sources
        }
      } catch (e) {
        this.showToast('Mənbələri yükləmək mümkün olmadı: ' + (e.response?.data?.error || e.message), 'error')
      } finally {
        this.loading = false
      }
    },

    async updateSource(id, updates) {
      try {
        const res = await api.post(`?endpoint=sources&action=update&id=${id}`, updates)
        if (res.data.success) {
          this.showToast('Mənbə tənzimləmələri yeniləndi')
          await this.fetchSources()
          await this.fetchStats()
        }
      } catch (e) {
        this.showToast('Xəta baş verdi: ' + (e.response?.data?.error || e.message), 'error')
      }
    },

    async grabSource(id) {
      this.grabbingSourceId = id
      try {
        const res = await api.post(`?endpoint=sources&action=grab&id=${id}`)
        if (res.data.success) {
          const r = res.data.result
          this.showToast(`${r.source_name || id}: ${r.new_items || 0} yeni xəbər tapıldı (${r.duplicates || 0} təkrar)`, 'success')
          await this.fetchSources()
          await this.fetchStats()
          await this.fetchNews()
        }
      } catch (e) {
        this.showToast('Grab xətası: ' + (e.response?.data?.error || e.message), 'error')
      } finally {
        this.grabbingSourceId = null
      }
    },

    async grabAllSources() {
      this.loading = true
      try {
        const res = await api.post('?endpoint=sources&action=grab-all')
        if (res.data.success) {
          this.showToast('Bütün aktiv mənbələrdən xəbərlər toplandı!', 'success')
          await this.fetchSources()
          await this.fetchStats()
          await this.fetchNews()
        }
      } catch (e) {
        this.showToast('Toplama xətası: ' + (e.response?.data?.error || e.message), 'error')
      } finally {
        this.loading = false
      }
    },

    async fetchNews(page = 1) {
      this.currentPage = page
      this.loading = true
      try {
        const params = new URLSearchParams({
          endpoint: 'news',
          page: this.currentPage,
          limit: this.newsLimit,
          status: this.filters.status,
          source_id: this.filters.source_id,
          search: this.filters.search
        })
        const res = await api.get(`?${params.toString()}`)
        if (res.data.success) {
          this.news = res.data.items
          this.totalNews = res.data.total
          this.totalPages = res.data.total_pages
        }
      } catch (e) {
        this.showToast('Xəbərləri yükləmək mümkün olmadı', 'error')
      } finally {
        this.loading = false
      }
    },

    async processNewsItem(id) {
      this.processingId = id
      try {
        const res = await api.post(`?endpoint=news&action=process&id=${id}`)
        if (res.data.success) {
          this.showToast('Xəbər uğurla yazıldı və alt404 qaralama (draft) kimi əlavə olundu!', 'success')
          const updatedItem = res.data.item || res.data.result?.item

          // Update in-place in this.news immediately
          if (this.news && this.news.length) {
            const found = this.news.find(n => n.id === id)
            if (found) {
              if (updatedItem) {
                Object.assign(found, updatedItem)
              } else if (res.data.result?.rewritten) {
                found.status = 'posted'
                found.rewritten_title = res.data.result.rewritten.title
                found.rewritten_excerpt = res.data.result.rewritten.excerpt
                found.rewritten_content = res.data.result.rewritten.content
                found.rewritten_tags = res.data.result.rewritten.tags
                found.posted_article_id = res.data.result.article_id
              }
            }
          }

          if (this.activeArticle && this.activeArticle.id === id) {
            if (updatedItem) {
              Object.assign(this.activeArticle, updatedItem)
            } else if (res.data.result?.rewritten) {
              this.activeArticle.status = 'posted'
              this.activeArticle.rewritten_title = res.data.result.rewritten.title
              this.activeArticle.rewritten_excerpt = res.data.result.rewritten.excerpt
              this.activeArticle.rewritten_content = res.data.result.rewritten.content
              this.activeArticle.rewritten_tags = res.data.result.rewritten.tags
              this.activeArticle.posted_article_id = res.data.result.article_id
            }
          }

          await this.fetchStats()
          return res.data
        }
      } catch (e) {
        this.showToast('Yazma xətası: ' + (e.response?.data?.error || e.message), 'error')
        throw e
      } finally {
        this.processingId = null
      }
    },

    async regenerateNewsItem(id) {
      this.processingId = id
      try {
        const res = await api.post(`?endpoint=news&action=regenerate&id=${id}`)
        if (res.data.success) {
          this.showToast('Xəbər OpenAI ilə yenidən yazıldı və veb qaralama yeniləndi!', 'success')
          const updatedItem = res.data.item || res.data.result?.item

          // Update in-place in this.news immediately
          if (this.news && this.news.length) {
            const found = this.news.find(n => n.id === id)
            if (found) {
              if (updatedItem) {
                Object.assign(found, updatedItem)
              } else if (res.data.result?.rewritten) {
                found.status = 'posted'
                found.rewritten_title = res.data.result.rewritten.title
                found.rewritten_excerpt = res.data.result.rewritten.excerpt
                found.rewritten_content = res.data.result.rewritten.content
                found.rewritten_tags = res.data.result.rewritten.tags
                found.posted_article_id = res.data.result.article_id
              }
            }
          }

          if (this.activeArticle && this.activeArticle.id === id) {
            if (updatedItem) {
              Object.assign(this.activeArticle, updatedItem)
            } else if (res.data.result?.rewritten) {
              this.activeArticle.status = 'posted'
              this.activeArticle.rewritten_title = res.data.result.rewritten.title
              this.activeArticle.rewritten_excerpt = res.data.result.rewritten.excerpt
              this.activeArticle.rewritten_content = res.data.result.rewritten.content
              this.activeArticle.rewritten_tags = res.data.result.rewritten.tags
              this.activeArticle.posted_article_id = res.data.result.article_id
            }
          }

          await this.fetchStats()
          return res.data
        }
      } catch (e) {
        this.showToast('Regenerasiya xətası: ' + (e.response?.data?.error || e.message), 'error')
        throw e
      } finally {
        this.processingId = null
      }
    },

    async batchProcess() {
      this.loading = true
      try {
        const res = await api.post('?endpoint=news&action=batch-process')
        if (res.data.success) {
          const count = res.data.processed?.length || 0
          const errCount = res.data.errors?.length || 0
          this.showToast(`${count} xəbər qaralama kimi paylaşıldı. ${errCount > 0 ? `(${errCount} xəta)` : ''}`, count > 0 ? 'success' : 'info')
          await this.fetchNews(this.currentPage)
          await this.fetchStats()
        }
      } catch (e) {
        this.showToast('Paket emal xətası: ' + (e.response?.data?.error || e.message), 'error')
      } finally {
        this.loading = false
      }
    },

    async toggleDuplicate(id) {
      try {
        const res = await api.post(`?endpoint=news&action=toggle-duplicate&id=${id}`)
        if (res.data.success) {
          this.showToast(res.data.is_duplicate ? 'Təkrar kimi işarələndi' : 'Təkrar statusu ləğv edildi')
          await this.fetchNews(this.currentPage)
          await this.fetchStats()
        }
      } catch (e) {
        this.showToast('Xəta: ' + (e.response?.data?.error || e.message), 'error')
      }
    },

    async retryNews(id) {
      try {
        const res = await api.post(`?endpoint=news&action=retry&id=${id}`)
        if (res.data.success) {
          this.showToast('Status sıfırlandı')
          await this.fetchNews(this.currentPage)
          await this.fetchStats()
        }
      } catch (e) {
        this.showToast('Xəta: ' + (e.response?.data?.error || e.message), 'error')
      }
    },

    async deleteNews(id) {
      if (!confirm('Bu xəbəri silmək istədiyinizdən əminsiniz?')) return
      try {
        const res = await api.delete(`?endpoint=news&id=${id}`)
        if (res.data.success) {
          this.showToast('Xəbər silindi')
          await this.fetchNews(this.currentPage)
          await this.fetchStats()
          if (this.activeArticle && this.activeArticle.id === id) {
            this.activeArticle = null
          }
        }
      } catch (e) {
        this.showToast('Xəta: ' + (e.response?.data?.error || e.message), 'error')
      }
    },

    async fetchSettings() {
      try {
        const res = await api.get('?endpoint=settings')
        if (res.data.success) {
          this.settings = res.data.settings
        }
      } catch (e) {
        this.showToast('Tənzimləmələri yükləmək mümkün olmadı', 'error')
      }
    },

    async saveSettings(settingsObj) {
      this.loading = true
      try {
        const res = await api.post('?endpoint=settings', { settings: settingsObj })
        if (res.data.success) {
          this.showToast('Tənzimləmələr uğurla saxlanıldı!', 'success')
          await this.fetchSettings()
        }
      } catch (e) {
        this.showToast('Saxlama xətası: ' + (e.response?.data?.error || e.message), 'error')
      } finally {
        this.loading = false
      }
    },

    async testOpenAI(key, model) {
      try {
        const res = await api.post('?endpoint=settings&action=test-openai', {
          openai_api_key: key,
          openai_model: model
        })
        return res.data
      } catch (e) {
        throw new Error(e.response?.data?.error || e.message)
      }
    },

    async testDatabase() {
      try {
        const res = await api.post('?endpoint=settings&action=test-db')
        return res.data?.status || {}
      } catch (e) {
        return { ai_db: false, web_db: false, ai_err: e.message }
      }
    }
  }
})
