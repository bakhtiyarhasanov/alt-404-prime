<template>
  <div class="ads-view">
    <div class="header-row">
      <h2 class="page-title">Məqalə İçi və Panel Reklamları</h2>
    </div>

    <!-- Ads list -->
    <div class="panel">
      <div class="ads-list">
        <div v-for="ad in ads" :key="ad.id" class="ad-card" :class="{ selected: selectedAd && selectedAd.id === ad.id }">
          <div class="ad-header">
            <span class="ad-title">{{ ad.label }}</span>
            <span class="ad-dims">{{ ad.width }} × {{ ad.height }}</span>
          </div>
          
          <div class="ad-preview">
            <img v-if="ad.image_url" :src="ad.image_url" class="preview-img" alt="">
            <div v-else class="preview-empty">Banner yoxdur</div>
          </div>

          <div class="ad-footer">
            <span class="status-badge" :class="{ active: ad.enabled }">
              {{ ad.enabled ? 'Aktiv' : 'Qeyri-aktiv' }}
            </span>
            <button @click="select(ad)" class="btn-ghost btn-sm">Düzəliş</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Settings Form Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content panel">
        <div class="modal-header">
          <h2 class="panel-title">Reklam Parametrləri: {{ selectedAd?.label }}</h2>
          <button @click="closeModal" class="close-btn">&times;</button>
        </div>
        <form @submit.prevent="save" class="form">
          <div class="form-group check-group">
            <input v-model="form.enabled" type="checkbox" id="enabled">
            <label for="enabled" class="check-label">Reklam Aktiv Edilsin</label>
          </div>

          <div class="form-group">
            <label class="label">Şəkil URL</label>
            <div class="upload-wrapper">
              <input v-model="form.image_url" type="text" class="input" placeholder="https://..." required>
              <input type="file" ref="fileInput" @change="uploadImage" style="display:none" accept="image/*">
              <button type="button" @click="$refs.fileInput.click()" class="btn-ghost" :disabled="uploadingImage">
                {{ uploadingImage ? 'Yüklənir...' : 'Şəkil Yüklə' }}
              </button>
            </div>
          </div>

          <div class="form-group">
            <label class="label">Keçid Linki (Target URL)</label>
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
    const showModal = ref(false)
    const uploadingImage = ref(false)
    const fileInput = ref(null)

    const form = reactive({
      enabled: false,
      image_url: '',
      link_url: ''
    })

    const fetchAds = async () => {
      try {
        const { data } = await client.get('/ads')
        ads.value = data
      } catch (err) {
        console.error(err)
      }
    }

    onMounted(fetchAds)

    const select = (ad) => {
      selectedAd.value = ad
      form.enabled = ad.enabled
      form.image_url = ad.image_url
      form.link_url = ad.link_url
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
        closeModal()
        await fetchAds()
      } catch (err) {
        alert('Xəta baş verdi')
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
      } catch (err) {
        alert('Şəkil yüklənərkən xəta baş verdi')
      } finally {
        uploadingImage.value = false
      }
    }

    return {
      ads,
      selectedAd,
      saving,
      showModal,
      uploadingImage,
      fileInput,
      form,
      select,
      closeModal,
      save,
      uploadImage
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
  margin: 0;
  color: #fff;
}
.panel {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.panel-title {
  font-size: 15px;
  font-weight: 600;
  margin: 0;
  color: #fff;
}
.ads-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}
.ad-card {
  background-color: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  transition: border-color 0.2s;
}
.ad-card.selected {
  border-color: var(--color-primary);
}
.ad-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.ad-title {
  font-weight: 600;
  color: #fff;
  font-size: 13px;
}
.ad-dims {
  font-size: 11px;
  color: var(--color-text-muted);
}
.ad-preview {
  height: 100px;
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 6px;
  overflow: hidden;
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
  font-size: 12px;
  color: var(--color-text-muted);
}
.ad-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.status-badge {
  font-size: 11px;
  color: var(--color-text-muted);
}
.status-badge.active {
  color: var(--color-green);
}
.btn-ghost {
  border: 1px solid var(--color-border);
  color: var(--color-text-secondary);
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  cursor: pointer;
  background: none;
  font-family: inherit;
}
.btn-ghost:hover {
  background-color: var(--color-surface-hover);
  color: #fff;
}
.btn-sm {
  font-size: 11px;
  padding: 6px 12px;
  border-radius: 6px;
}
.upload-wrapper {
  display: flex;
  gap: 8px;
}
.upload-wrapper .input {
  flex: 1;
}
.form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.label {
  font-size: 11px;
  color: var(--color-text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
}
.input {
  background-color: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 8px 12px;
  color: #fff;
  font-family: inherit;
  outline: none;
  font-size: 13px;
}
.input:focus {
  border-color: var(--color-primary);
}
.check-group {
  flex-direction: row;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}
.check-label {
  font-size: 13px;
  color: var(--color-text-primary);
  user-select: none;
}
.actions-row {
  display: flex;
  gap: 8px;
}
.btn-primary {
  background-color: var(--color-primary);
  color: #111;
  font-weight: 600;
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 13px;
  cursor: pointer;
  border: none;
}
.btn-primary:hover {
  background-color: var(--color-primary-hover);
}

/* Modal Overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  animation: fadeIn 0.2s ease-out;
}

.modal-content {
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.2s ease-out;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.close-btn {
  background: none;
  border: none;
  color: var(--color-text-secondary);
  font-size: 24px;
  cursor: pointer;
  transition: color 0.2s;
  line-height: 1;
  padding: 0;
}

.close-btn:hover {
  color: #fff;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
</style>
