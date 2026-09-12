<template>
  <div class="ads-view">
    <div class="header-row">
      <div>
        <h2 class="page-title">Reklam Bannerləri və Zonaları</h2>
        <p class="page-subtitle">Saytın ana səhifə, kateqoriya və məqalə daxilindəki reklam sahələrini idarə edin</p>
      </div>
      <button @click="fetchAds" class="btn-ghost" :disabled="loading" title="Yenilə">
        <svg class="icon" :class="{ spinning: loading }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
        </svg>
        <span>Yenilə</span>
      </button>
    </div>

    <!-- Toast alert -->
    <transition name="fade">
      <div v-if="toast" class="toast-banner" :class="toast.type">
        <svg v-if="toast.type === 'success'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span>{{ toast.message }}</span>
      </div>
    </transition>

    <!-- Ads list -->
    <div class="panel">
      <div v-if="loading && ads.length === 0" class="empty-state">
        Yüklənir...
      </div>

      <div v-else class="ads-list">
        <div v-for="ad in ads" :key="ad.id" class="ad-card" :class="{ disabled: !ad.enabled }">
          
          <div class="ad-card-top">
            <div class="ad-info">
              <span class="ad-location-badge">{{ getPlacement(ad.id).tag }}</span>
              <h3 class="ad-card-title">{{ ad.label || getPlacement(ad.id).name }}</h3>
              <p class="ad-card-desc">{{ getPlacement(ad.id).desc }}</p>
            </div>
            
            <!-- Quick Toggle Switch -->
            <div class="toggle-wrapper" :title="ad.enabled ? 'Deaktiv etmək üçün klikləyin' : 'Aktiv etmək üçün klikləyin'">
              <label class="switch">
                <input 
                  type="checkbox" 
                  :checked="ad.enabled" 
                  :disabled="togglingId === ad.id"
                  @change="toggleStatus(ad)"
                >
                <span class="slider round"></span>
              </label>
              <span class="toggle-label" :class="{ active: ad.enabled }">
                {{ ad.enabled ? 'Aktiv' : 'Qeyri-aktiv' }}
              </span>
            </div>
          </div>

          <!-- Preview image box -->
          <div class="ad-preview-box" :style="{ aspectRatio: getPlacement(ad.id).aspectCss }">
            <template v-if="ad.image_url">
              <img :src="ad.image_url" class="preview-img" alt="Reklam Banneri" loading="lazy">
              <a 
                v-if="ad.link_url && ad.link_url !== '#'" 
                :href="ad.link_url" 
                target="_blank" 
                rel="noopener noreferrer" 
                class="preview-overlay-btn"
                title="Linki aç"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                  <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                </svg>
                <span>Keçid linkini aç</span>
              </a>
            </template>
            <div v-else class="preview-empty">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                <path d="M20.42 17.58L15 12l-4 4-2-2-4.42 4.42"/>
              </svg>
              <span>Banner şəkli yoxdur</span>
            </div>
          </div>

          <!-- Card footer -->
          <div class="ad-card-footer">
            <div class="dims-group">
              <span class="dims-label">Tövsiyə olunan ölçü:</span>
              <span class="dims-val">{{ ad.width || getPlacement(ad.id).width }} × {{ ad.height || getPlacement(ad.id).height }} px</span>
            </div>
            
            <button @click="select(ad)" class="btn-edit">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
              </svg>
              <span>Düzəliş et</span>
            </button>
          </div>

        </div>
      </div>
    </div>

    <!-- Settings Form Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content panel">
        <div class="modal-header">
          <div>
            <span class="modal-tag">{{ getPlacement(selectedAd?.id).tag }}</span>
            <h2 class="panel-title">{{ selectedAd?.label }}</h2>
          </div>
          <button @click="closeModal" class="close-btn">&times;</button>
        </div>

        <div class="info-alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
          </svg>
          <div>
            <p><strong>Yerləşmə:</strong> {{ getPlacement(selectedAd?.id).desc }}</p>
            <p><strong>Tövsiyə olunan ölçü:</strong> {{ getPlacement(selectedAd?.id).dims }} (Nisbət: {{ getPlacement(selectedAd?.id).aspect }})</p>
          </div>
        </div>

        <form @submit.prevent="save" class="form">
          <div class="form-group check-group">
            <label class="switch-small">
              <input v-model="form.enabled" type="checkbox" id="enabled">
              <span class="slider round"></span>
            </label>
            <label for="enabled" class="check-label">
              <strong>Reklam Aktiv Edilsin</strong>
              <small class="help-text">Deaktiv edildikdə banner saytdan dərhal gizlədilir.</small>
            </label>
          </div>

          <div class="form-group">
            <label class="label">Banner Şəkli</label>
            <div class="modal-preview-card" v-if="form.image_url">
              <img :src="form.image_url" alt="Önizləmə" class="modal-preview-img">
            </div>
            <div class="upload-wrapper">
              <input v-model="form.image_url" type="text" class="input" placeholder="https://..." required>
              <input type="file" ref="fileInput" @change="uploadImage" style="display:none" accept="image/*">
              <button type="button" @click="$refs.fileInput.click()" class="btn-ghost upload-btn" :disabled="uploadingImage">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                <span>{{ uploadingImage ? 'Yüklənir...' : 'Fayl Seç' }}</span>
              </button>
            </div>
          </div>

          <div class="form-group">
            <div class="label-row">
              <label class="label">Keçid Linki (Target URL)</label>
              <a v-if="form.link_url && form.link_url !== '#'" :href="form.link_url" target="_blank" class="test-link">
                Yoxla ↗
              </a>
            </div>
            <input v-model="form.link_url" type="text" class="input" placeholder="https://..." required>
          </div>

          <div class="actions-row">
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? 'Yadda saxlanılır...' : 'Yadda Saxla' }}
            </button>
            <button type="button" @click="closeModal" class="btn-ghost">Ləğv et</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import client from '../api/client'

export default {
  name: 'AdsView',
  setup() {
    const ads = ref([])
    const selectedAd = ref(null)
    const saving = ref(false)
    const loading = ref(false)
    const togglingId = ref(null)
    const showModal = ref(false)
    const uploadingImage = ref(false)
    const fileInput = ref(null)
    const toast = ref(null)

    const form = reactive({
      enabled: false,
      image_url: '',
      link_url: ''
    })

    const placements = {
      'spotlight': {
        name: 'Spotlight Reklam Kartı',
        tag: 'Ana Səhifə — 4-cü Kart',
        desc: 'Ana səhifənin ən üst blokunda 4-cü kart kimi çıxır. Deaktiv edildikdə yerinə 4-cü xəbər göstərilir.',
        aspect: '4:5 Portrait',
        aspectCss: '4 / 5',
        dims: '400 × 500 px',
        width: 400,
        height: 500
      },
      'leaderboard': {
        name: '1-ci Əsas Promo Banner',
        tag: 'Ana Səhifə — Üst Banner',
        desc: 'Videolar qalereyasından dərhal sonra, kateqoriya xəbərlərindən əvvəl çıxan əsas geniş banner.',
        aspect: '6:1 Horizontal',
        aspectCss: '12 / 2.2',
        dims: '1200 × 200 px',
        width: 1200,
        height: 200
      },
      'home-promo-2': {
        name: '2-ci Promo Banner',
        tag: 'Ana Səhifə — Bölmələrarası',
        desc: '2-ci kateqoriya bölməsindən sonra çıxan bölmələrarası horizontal reklam banneri.',
        aspect: '6:1 Horizontal',
        aspectCss: '12 / 2.2',
        dims: '1200 × 200 px',
        width: 1200,
        height: 200
      },
      'inline': {
        name: 'Məqalə İçi Reklam',
        tag: 'Məqalə Səhifəsi',
        desc: 'Tək məqalə oxuma səhifəsində teqlərdən sonra, əlaqəli xəbərlərdən əvvəl göstərilir.',
        aspect: '5:1 Horizontal',
        aspectCss: '5 / 1',
        dims: '800 × 160 px',
        width: 800,
        height: 160
      },
      'category-banner': {
        name: 'Kateqoriya Səhifəsi Banneri',
        tag: 'Bütün Kateqoriyalar',
        desc: 'Kateqoriya səhifələrində başlıq blokunun altında və xəbər kataloqunun üstündə görünür.',
        aspect: '6:1 Horizontal',
        aspectCss: '12 / 2.2',
        dims: '1200 × 200 px',
        width: 1200,
        height: 200
      }
    }

    const getPlacement = (id) => {
      return placements[id] || {
        name: id,
        tag: 'Ümumi Reklam',
        desc: 'Veb-sayt reklam sahəsi.',
        aspect: 'Standard',
        aspectCss: '16 / 9',
        dims: 'Banner',
        width: 300,
        height: 250
      }
    }

    const showToast = (message, type = 'success') => {
      toast.value = { message, type }
      setTimeout(() => {
        if (toast.value && toast.value.message === message) {
          toast.value = null
        }
      }, 3500)
    }

    const fetchAds = async () => {
      loading.value = true
      try {
        const { data } = await client.get('/ads')
        ads.value = Array.isArray(data) ? data : []
      } catch (err) {
        console.error(err)
        showToast('Reklamları yükləmək mümkün olmadı', 'error')
      } finally {
        loading.value = false
      }
    }

    onMounted(fetchAds)

    const toggleStatus = async (ad) => {
      const nextStatus = !ad.enabled
      togglingId.value = ad.id
      
      // Optimistic update
      ad.enabled = nextStatus

      try {
        await client.put(`/ads/${ad.id}`, {
          enabled: nextStatus
        })
        showToast(nextStatus ? `"${ad.label}" aktivləşdirildi` : `"${ad.label}" deaktiv edildi`)
      } catch (err) {
        // Rollback
        ad.enabled = !nextStatus
        showToast('Status yenilənərkən xəta baş verdi', 'error')
      } finally {
        togglingId.value = null
      }
    }

    const select = (ad) => {
      selectedAd.value = ad
      form.enabled = Boolean(ad.enabled)
      form.image_url = ad.image_url || ''
      form.link_url = ad.link_url || ''
      showModal.value = true
    }

    const closeModal = () => {
      showModal.value = false
      selectedAd.value = null
      form.enabled = false
      form.image_url = ''
      form.link_url = ''
    }

    const save = async () => {
      saving.value = true
      try {
        await client.put(`/ads/${selectedAd.value.id}`, form)
        showToast('Reklam parametrləri uğurla yadda saxlanıldı')
        closeModal()
        await fetchAds()
      } catch (err) {
        showToast('Yadda saxlanarkən xəta baş verdi', 'error')
      } finally {
        saving.value = false
      }
    }

    const uploadImage = async (e) => {
      const file = e.target.files[0]
      if (!file) return
      uploadingImage.value = true
      const formData = new FormData()
      formData.append('file', file)
      formData.append('title', file.name)
      formData.append('alt_text', file.name)

      try {
        const { data } = await client.post('/media', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        
        let baseUrl = client.defaults.baseURL || ''
        if (baseUrl.endsWith('/api')) {
          baseUrl = baseUrl.slice(0, -4)
        }
        
        if (data.url && data.url.startsWith('/')) {
          form.image_url = baseUrl + data.url
        } else {
          form.image_url = data.url
        }
        showToast('Şəkil uğurla yükləndi')
      } catch (err) {
        showToast('Şəkil yüklənərkən xəta baş verdi', 'error')
      } finally {
        uploadingImage.value = false
        if (fileInput.value) fileInput.value.value = ''
      }
    }

    return {
      ads,
      selectedAd,
      saving,
      loading,
      togglingId,
      showModal,
      uploadingImage,
      fileInput,
      form,
      toast,
      getPlacement,
      toggleStatus,
      select,
      closeModal,
      save,
      uploadImage,
      fetchAds
    }
  }
}
</script>

<style scoped>
.ads-view {
  width: 100%;
}
.header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  gap: 16px;
}
.page-title {
  font-size: 20px;
  font-weight: 700;
  margin: 0 0 4px 0;
  color: var(--color-text-primary, #fff);
}
.page-subtitle {
  font-size: 13px;
  color: var(--color-text-secondary, rgba(255, 255, 255, 0.6));
  margin: 0;
}

/* Toast alert */
.toast-banner {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 18px;
  border-radius: 8px;
  margin-bottom: 20px;
  font-size: 13px;
  font-weight: 500;
}
.toast-banner.success {
  background: rgba(34, 197, 94, 0.12);
  border: 1px solid rgba(34, 197, 94, 0.3);
  color: #4ade80;
}
.toast-banner.error {
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #f87171;
}

.panel {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 24px;
}
.empty-state {
  padding: 40px;
  text-align: center;
  color: var(--color-text-muted);
  font-size: 14px;
}
.ads-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 24px;
}

/* Ad Card */
.ad-card {
  background-color: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: 10px;
  padding: 18px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  transition: all 0.2s ease;
}
.ad-card:hover {
  border-color: rgba(252, 219, 86, 0.4);
}
.ad-card.disabled {
  opacity: 0.72;
}

.ad-card-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
}
.ad-info {
  flex: 1;
}
.ad-location-badge {
  display: inline-block;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 3px 8px;
  border-radius: 4px;
  background: rgba(252, 219, 86, 0.15);
  color: #fcdb56;
  margin-bottom: 6px;
}
.ad-card-title {
  font-size: 14px;
  font-weight: 600;
  color: #fff;
  margin: 0 0 4px 0;
  line-height: 1.3;
}
.ad-card-desc {
  font-size: 11px;
  color: var(--color-text-secondary, rgba(255, 255, 255, 0.6));
  margin: 0;
  line-height: 1.4;
}

/* Switch */
.toggle-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  shrink: 0;
}
.switch {
  position: relative;
  display: inline-block;
  width: 38px;
  height: 22px;
}
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.15);
  transition: .3s;
  border-radius: 22px;
}
.slider:before {
  position: absolute;
  content: "";
  height: 16px;
  width: 16px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .3s;
  border-radius: 50%;
}
input:checked + .slider {
  background-color: #22c55e;
}
input:checked + .slider:before {
  transform: translateX(16px);
}
.toggle-label {
  font-size: 10px;
  color: var(--color-text-muted);
  font-weight: 500;
}
.toggle-label.active {
  color: #4ade80;
  font-weight: 600;
}

/* Preview Box */
.ad-preview-box {
  width: 100%;
  max-height: 180px;
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  overflow: hidden;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}
.preview-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.preview-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  color: var(--color-text-muted);
}
.preview-overlay-btn {
  position: absolute;
  bottom: 8px;
  right: 8px;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(4px);
  color: #fff;
  font-size: 10px;
  padding: 4px 8px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  gap: 4px;
  text-decoration: none;
  opacity: 0;
  transition: opacity 0.2s ease;
}
.ad-preview-box:hover .preview-overlay-btn {
  opacity: 1;
}

/* Card footer */
.ad-card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 6px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}
.dims-group {
  display: flex;
  flex-direction: column;
}
.dims-label {
  font-size: 9px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--color-text-muted);
}
.dims-val {
  font-size: 11px;
  font-weight: 600;
  color: var(--color-text-secondary);
}

.btn-edit {
  display: flex;
  align-items: center;
  gap: 6px;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  color: var(--color-text-primary);
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}
.btn-edit:hover {
  background: var(--color-surface-hover);
  border-color: #fcdb56;
  color: #fcdb56;
}

.btn-ghost {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: 1px solid var(--color-border);
  color: var(--color-text-secondary);
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 12px;
  cursor: pointer;
  background: none;
}
.btn-ghost:hover {
  background-color: var(--color-surface-hover);
  color: #fff;
}
.icon {
  width: 14px;
  height: 14px;
}
.spinning {
  animation: spin 1s infinite linear;
}
@keyframes spin {
  100% { transform: rotate(360deg); }
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(8px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}
.modal-content {
  width: 100%;
  max-width: 520px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}
.modal-tag {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  color: #fcdb56;
  display: block;
  margin-bottom: 4px;
}
.close-btn {
  background: none;
  border: none;
  color: var(--color-text-secondary);
  font-size: 24px;
  cursor: pointer;
  padding: 0;
}
.close-btn:hover {
  color: #fff;
}

.info-alert {
  display: flex;
  gap: 10px;
  padding: 12px;
  background: rgba(252, 219, 86, 0.08);
  border: 1px solid rgba(252, 219, 86, 0.2);
  border-radius: 8px;
  margin-bottom: 20px;
  font-size: 11.5px;
  color: rgba(255, 255, 255, 0.85);
  line-height: 1.5;
}
.info-alert p {
  margin: 0 0 4px 0;
}
.info-alert p:last-child {
  margin-bottom: 0;
}
.info-alert svg {
  color: #fcdb56;
  shrink: 0;
  margin-top: 2px;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.label {
  font-size: 11px;
  color: var(--color-text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
}
.test-link {
  font-size: 11px;
  color: #fcdb56;
  text-decoration: none;
}
.test-link:hover {
  text-decoration: underline;
}

.modal-preview-card {
  width: 100%;
  max-height: 140px;
  border-radius: 6px;
  overflow: hidden;
  border: 1px solid var(--color-border);
  margin-bottom: 8px;
  background: #000;
  display: flex;
  align-items: center;
  justify-content: center;
}
.modal-preview-img {
  max-width: 100%;
  max-height: 140px;
  object-fit: contain;
}

.upload-wrapper {
  display: flex;
  gap: 8px;
}
.upload-wrapper .input {
  flex: 1;
}
.upload-btn {
  white-space: nowrap;
}

.input {
  background-color: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 10px 12px;
  color: #fff;
  font-family: inherit;
  outline: none;
  font-size: 13px;
}
.input:focus {
  border-color: #fcdb56;
}

.switch-small {
  position: relative;
  display: inline-block;
  width: 36px;
  height: 20px;
  shrink: 0;
}
.switch-small input {
  opacity: 0;
  width: 0;
  height: 0;
}
.switch-small .slider {
  border-radius: 20px;
}
.switch-small .slider:before {
  height: 14px;
  width: 14px;
  left: 3px;
  bottom: 3px;
}
.switch-small input:checked + .slider:before {
  transform: translateX(16px);
}

.check-group {
  display: flex;
  flex-direction: row;
  align-items: flex-start;
  gap: 12px;
  padding: 12px;
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  cursor: pointer;
}
.check-label {
  display: flex;
  flex-direction: column;
  gap: 2px;
  cursor: pointer;
}
.check-label strong {
  font-size: 13px;
  color: #fff;
}
.help-text {
  font-size: 11px;
  color: var(--color-text-secondary);
}

.actions-row {
  display: flex;
  gap: 10px;
  margin-top: 8px;
}
.btn-primary {
  background-color: #fcdb56;
  color: #080117;
  font-weight: 700;
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 13px;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}
.btn-primary:hover {
  background-color: #ebd04f;
}
</style>
