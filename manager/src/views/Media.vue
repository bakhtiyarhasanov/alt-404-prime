<template>
  <div class="media-view">
    <div class="panel">
      <!-- Top upload bar -->
      <div class="upload-bar">
        <input type="file" ref="fileInput" @change="uploadFile" style="display:none" accept="image/*">
        <button @click="$refs.fileInput.click()" class="btn-primary" :disabled="uploading">
          {{ uploading ? 'Yüklənir...' : 'Yeni Şəkil Yüklə' }}
        </button>
      </div>

      <!-- Media grid -->
      <div class="media-grid">
        <div v-for="item in media" :key="item.id" class="media-card">
          <div class="card-preview">
            <img :src="item.url" class="media-img" alt="">
          </div>
          <div class="card-info">
            <span class="file-name">{{ item.file_name }}</span>
            <input type="text" readonly :value="item.url" @click="copyUrl" class="url-input">
          </div>
          <div class="card-actions">
            <button @click="confirmDelete(item)" class="action-btn delete">Sil</button>
          </div>
        </div>
        <div v-if="media.length === 0" class="empty-media">Heç bir media faylı yoxdur.</div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import client from '../api/client'

export default {
  name: 'MediaView',
  setup() {
    const media = ref([])
    const uploading = ref(false)

    const fetchMedia = async () => {
      try {
        const { data } = await client.get('/media')
        media.value = data
      } catch (err) {
        console.error(err)
      }
    }

    onMounted(fetchMedia)

    const uploadFile = async (e) => {
      const file = e.target.files[0]
      if (!file) return
      uploading.value = true
      const formData = new FormData()
      formData.append('file', file)
      formData.append('title', file.name)
      formData.append('alt_text', file.name)

      try {
        await client.post('/media', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        await fetchMedia()
      } catch (err) {
        alert('Şəkil yüklənərkən xəta baş verdi')
      } finally {
        uploading.value = false
      }
    }

    const copyUrl = (e) => {
      e.target.select()
      document.execCommand('copy')
      alert('Keçid linki kopyalandı!')
    }

    const confirmDelete = async (item) => {
      if (confirm('Bu media faylını silmək istədiyinizdən əminsiniz?')) {
        try {
          await client.delete(`/media/${item.id}`)
          await fetchMedia()
        } catch (err) {
          alert('Xəta baş verdi')
        }
      }
    }

    return {
      media,
      uploading,
      uploadFile,
      copyUrl,
      confirmDelete
    }
  }
}
</script>

<style scoped>
.panel {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 24px;
}
.upload-bar {
  margin-bottom: 24px;
}
.btn-primary {
  background-color: var(--color-primary);
  color: #111;
  font-weight: 600;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
}
.btn-primary:hover {
  background-color: var(--color-primary-hover);
}
.media-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}
.media-card {
  background-color: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
.card-preview {
  height: 140px;
  background-color: var(--color-surface);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}
.media-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.card-info {
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}
.file-name {
  font-size: 12px;
  font-weight: 600;
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.url-input {
  width: 100%;
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 4px;
  padding: 4px 8px;
  color: var(--color-text-secondary);
  font-size: 11px;
  outline: none;
  cursor: pointer;
}
.card-actions {
  padding: 8px 12px;
  border-top: 1px solid var(--color-border);
  display: flex;
  justify-content: flex-end;
}
.action-btn {
  font-size: 11px;
  padding: 4px 8px;
  border-radius: 4px;
  border: 1px solid var(--color-border);
  cursor: pointer;
  background: none;
  font-family: inherit;
  transition: all 0.2s;
}
.action-btn.delete {
  color: var(--color-red);
}
.action-btn.delete:hover {
  background-color: rgba(239, 68, 68, 0.05);
}
.empty-media {
  grid-column: span 4;
  text-align: center;
  color: var(--color-text-muted);
  padding: 48px;
}
</style>
