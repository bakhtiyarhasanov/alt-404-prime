<template>
  <div class="videos-view">
    <div class="grid">
      <!-- Videos list -->
      <div class="panel">
        <h2 class="panel-title">Məqalə Səhifəsi Videoları</h2>
        <div class="table-wrapper">
          <table class="table">
            <thead>
              <tr>
                <th>Video</th>
                <th>Keçid</th>
                <th>Sıra</th>
                <th class="actions-col">Əməliyyatlar</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="vid in videos" :key="vid.id">
                <td>
                  <div class="vid-cell">
                    <span class="vid-title">{{ vid.title }}</span>
                    <img :src="vid.thumbnail_url" class="vid-thumb" alt="" v-if="vid.thumbnail_url">
                  </div>
                </td>
                <td>
                  <a :href="vid.youtube_url" target="_blank" class="yt-link">YouTube Keçidi</a>
                </td>
                <td>{{ vid.sort_order }}</td>
                <td>
                  <div class="actions">
                    <button @click="edit(vid)" class="action-btn edit">Düzəliş</button>
                    <button @click="confirmDelete(vid)" class="action-btn delete">Sil</button>
                  </div>
                </td>
              </tr>
              <tr v-if="videos.length === 0">
                <td colspan="4" class="empty-cell">Heç bir video tapılmadı.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Add/Edit form -->
      <div class="panel">
        <h2 class="panel-title">{{ isEdit ? 'Videonu Redaktə Et' : 'Yeni Video' }}</h2>
        <form @submit.prevent="save" class="form">
          <div class="form-group">
            <label class="label">Video Başlığı</label>
            <input v-model="form.title" type="text" placeholder="Video başlığını yazın..." class="input" required>
          </div>

          <div class="form-group">
            <label class="label">YouTube URL</label>
            <input v-model="form.youtube_url" type="text" placeholder="https://www.youtube.com/watch?v=..." class="input" required>
          </div>

          <div class="form-group">
            <label class="label">Thumbnail (Önşəkil) URL</label>
            <input v-model="form.thumbnail_url" type="text" placeholder="https://..." class="input">
          </div>

          <div class="form-group">
            <label class="label">Sıralama Nömrəsi</label>
            <input v-model.number="form.sort_order" type="number" class="input" required>
          </div>

          <div class="actions-row">
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? 'Yadda saxlanılır...' : 'Yadda Saxla' }}
            </button>
            <button v-if="isEdit" type="button" @click="resetForm" class="btn-ghost">Ləğv et</button>
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
  name: 'VideosView',
  setup() {
    const videos = ref([])
    const isEdit = ref(false)
    const saving = ref(false)
    const editingId = ref(null)

    const form = reactive({
      title: '',
      youtube_url: '',
      thumbnail_url: '',
      sort_order: 0
    })

    const fetchVideos = async () => {
      try {
        const { data } = await client.get('/videos')
        videos.value = data
      } catch (err) {
        console.error(err)
      }
    }

    onMounted(fetchVideos)

    const edit = (vid) => {
      isEdit.value = true
      editingId.value = vid.id
      form.title = vid.title
      form.youtube_url = vid.youtube_url
      form.thumbnail_url = vid.thumbnail_url
      form.sort_order = vid.sort_order
    }

    const resetForm = () => {
      isEdit.value = false
      editingId.value = null
      form.title = ''
      form.youtube_url = ''
      form.thumbnail_url = ''
      form.sort_order = 0
    }

    const save = async () => {
      saving.value = true
      try {
        if (isEdit.value) {
          await client.put(`/videos/${editingId.value}`, form)
        } else {
          await client.post('/videos', form)
        }
        resetForm()
        await fetchVideos()
      } catch (err) {
        alert(err.response?.data?.error || 'Xəta baş verdi')
      } finally {
        saving.value = false
      }
    }

    const confirmDelete = async (vid) => {
      if (confirm(`"${vid.title}" videosunu silmək istədiyinizdən əminsiniz?`)) {
        try {
          await client.delete(`/videos/${vid.id}`)
          await fetchVideos()
        } catch (err) {
          alert('Xəta baş verdi')
        }
      }
    }

    return {
      videos,
      isEdit,
      saving,
      form,
      edit,
      resetForm,
      save,
      confirmDelete
    }
  }
}
</script>

<style scoped>
.grid {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 24px;
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
.table-wrapper {
  overflow-x: auto;
}
.table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}
.table th, .table td {
  padding: 14px 18px;
  border-bottom: 1px solid var(--color-border);
  font-size: 13px;
}
.table th {
  background-color: rgba(255,255,255,0.01);
  color: var(--color-text-secondary);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 10px;
  letter-spacing: 0.05em;
}
.vid-cell {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.vid-title {
  font-weight: 600;
  color: #fff;
}
.vid-thumb {
  width: 80px;
  height: 60px;
  border-radius: 6px;
  object-fit: cover;
}
.yt-link {
  color: var(--color-primary);
  text-decoration: none;
}
.actions {
  display: flex;
  gap: 8px;
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
.actions-row {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}
.btn-primary {
  background-color: var(--color-primary);
  color: #111;
  font-weight: 600;
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 13px;
  cursor: pointer;
}
.btn-primary:hover {
  background-color: var(--color-primary-hover);
}
.btn-ghost {
  border: 1px solid var(--color-border);
  color: var(--color-text-secondary);
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 13px;
  background: none;
  cursor: pointer;
}
.btn-ghost:hover {
  background-color: var(--color-surface-hover);
  color: #fff;
}
.empty-cell {
  text-align: center;
  color: var(--color-text-muted);
  padding: 48px;
}
</style>
