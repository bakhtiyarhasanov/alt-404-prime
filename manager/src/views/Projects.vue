<template>
  <div class="projects-view">
    <div class="grid">
      <!-- Projects list -->
      <div class="panel">
        <div class="panel-header">
          <h2 class="panel-title">Xüsusi Layihələr (Posterlər)</h2>
          <span class="count-badge">{{ projects.length }} layihə</span>
        </div>

        <div class="table-wrapper">
          <table class="table">
            <thead>
              <tr>
                <th>Poster / Layihə</th>
                <th>Bölmə & Müddət</th>
                <th>Status</th>
                <th>Sıra</th>
                <th class="actions-col">Əməliyyatlar</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="proj in projects" :key="proj.id">
                <td>
                  <div class="project-cell">
                    <img
                      :src="proj.image || getYoutubeThumb(proj.youtube_url)"
                      class="project-thumb"
                      alt=""
                      v-if="proj.image || getYoutubeThumb(proj.youtube_url)"
                    />
                    <div class="project-info">
                      <span class="project-title">{{ proj.title }}</span>
                      <span class="project-subtitle" v-if="proj.subtitle">{{ proj.subtitle }}</span>
                      <a :href="proj.youtube_url" target="_blank" class="yt-link">
                        <svg class="icon-inline" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        YouTube-da bax
                      </a>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="meta-cell">
                    <span class="badge-cat">{{ proj.category }}</span>
                    <span class="badge-dur" v-if="proj.duration">{{ proj.duration }}</span>
                  </div>
                </td>
                <td>
                  <span :class="['status-badge', proj.enabled ? 'active' : 'inactive']">
                    {{ proj.enabled ? 'Aktiv' : 'Deaktiv' }}
                  </span>
                </td>
                <td>{{ proj.sort_order }}</td>
                <td>
                  <div class="actions">
                    <button @click="edit(proj)" class="action-btn edit" title="Düzəliş et">Düzəliş</button>
                    <button @click="confirmDelete(proj)" class="action-btn delete" title="Sil">Sil</button>
                  </div>
                </td>
              </tr>
              <tr v-if="projects.length === 0">
                <td colspan="5" class="empty-cell">Heç bir layihə tapılmadı.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Add/Edit form -->
      <div class="panel">
        <h2 class="panel-title">{{ isEdit ? 'Layihəni Redaktə Et' : 'Yeni Layihə Əlavə Et' }}</h2>
        <form @submit.prevent="save" class="form">
          <div class="form-group">
            <label class="label">Layihə Başlığı *</label>
            <input v-model="form.title" type="text" placeholder="məs: CHATGPT İNSAN OLSAYDI NECƏ GÖRÜNƏRDİ?" class="input" required />
          </div>

          <div class="form-group">
            <label class="label">Alt Başlıq / Qısa Xülasə</label>
            <input v-model="form.subtitle" type="text" placeholder="Neyroşəbəkənin psixoloji portreti..." class="input" />
          </div>

          <div class="form-row">
            <div class="form-group flex-1">
              <label class="label">Bölmə / Kateqoriya</label>
              <input v-model="form.category" list="cat-suggestions" type="text" placeholder="məs: EKSPERİMENT" class="input" required />
              <datalist id="cat-suggestions">
                <option value="EKSPERİMENT" />
                <option value="DOKUMENTAL" />
                <option value="TƏDQİQAT" />
                <option value="SOSİAL MEDİA" />
                <option value="OYUN TƏHLİLİ" />
                <option value="KRİPTO & BİZNES" />
                <option value="XÜSUSİ BURAXILIŞ" />
              </datalist>
            </div>

            <div class="form-group" style="width: 130px;">
              <label class="label">Müddət</label>
              <input v-model="form.duration" type="text" placeholder="28:15" class="input" />
            </div>
          </div>

          <div class="form-group">
            <label class="label">YouTube Video Keçidi *</label>
            <input
              v-model="form.youtube_url"
              @input="onYoutubeChange"
              type="text"
              placeholder="https://www.youtube.com/watch?v=..."
              class="input"
              required
            />
          </div>

          <div class="form-group">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <label class="label">Poster Şəkli URL *</label>
              <button
                type="button"
                @click="fetchYtThumb"
                style="font-size: 10px; color: var(--color-primary); background: none; border: none; cursor: pointer;"
              >
                YouTube-dan çək
              </button>
            </div>
            <input
              v-model="form.image"
              type="text"
              placeholder="https://... (9:16 formatlı poster və ya video şəkli)"
              class="input"
              required
            />
            <!-- Poster Live Preview -->
            <div v-if="form.image || getYoutubeThumb(form.youtube_url)" class="poster-preview-box">
              <span class="preview-label">Önbaxış (Poster):</span>
              <img :src="form.image || getYoutubeThumb(form.youtube_url)" class="poster-preview-img" alt="Poster Preview" />
            </div>
          </div>

          <div class="form-group">
            <label class="label">Ətraflı Təsvir</label>
            <textarea
              v-model="form.description"
              rows="3"
              placeholder="Layihə və buraxılış haqqında qısa təsvir..."
              class="input"
              style="resize: vertical; font-family: inherit;"
            ></textarea>
          </div>

          <div class="form-row">
            <div class="form-group flex-1">
              <label class="label">Sıralama Nömrəsi</label>
              <input v-model.number="form.sort_order" type="number" class="input" required />
            </div>

            <div class="form-group flex-1" style="justify-content: flex-end; padding-bottom: 4px;">
              <label class="checkbox-label">
                <input v-model="form.enabled" type="checkbox" class="checkbox" />
                <span>Saytda aktiv olsun</span>
              </label>
            </div>
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
  name: 'ProjectsView',
  setup() {
    const projects = ref([])
    const isEdit = ref(false)
    const saving = ref(false)
    const editingId = ref(null)

    const form = reactive({
      title: '',
      subtitle: '',
      category: 'EKSPERİMENT',
      image: '',
      duration: '15:00',
      youtube_url: '',
      description: '',
      sort_order: 0,
      enabled: true
    })

    const extractYoutubeId = (url) => {
      if (!url) return null
      const reg = /(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/
      const match = url.match(reg)
      return match ? match[1] : null
    }

    const getYoutubeThumb = (url) => {
      const id = extractYoutubeId(url)
      return id ? `https://img.youtube.com/vi/${id}/hqdefault.jpg` : ''
    }

    const onYoutubeChange = () => {
      if (!form.image || form.image.includes('img.youtube.com')) {
        const thumb = getYoutubeThumb(form.youtube_url)
        if (thumb) form.image = thumb
      }
    }

    const fetchYtThumb = () => {
      const thumb = getYoutubeThumb(form.youtube_url)
      if (thumb) {
        form.image = thumb
      } else {
        alert('Düzgün YouTube video linki daxil edin')
      }
    }

    const fetchProjects = async () => {
      try {
        const { data } = await client.get('/projects?all=1')
        projects.value = data
      } catch (err) {
        console.error(err)
      }
    }

    onMounted(fetchProjects)

    const edit = (proj) => {
      isEdit.value = true
      editingId.value = proj.id
      form.title = proj.title
      form.subtitle = proj.subtitle || ''
      form.category = proj.category || 'EKSPERİMENT'
      form.image = proj.image || getYoutubeThumb(proj.youtube_url)
      form.duration = proj.duration || '15:00'
      form.youtube_url = proj.youtube_url
      form.description = proj.description || ''
      form.sort_order = proj.sort_order || 0
      form.enabled = typeof proj.enabled !== 'undefined' ? Boolean(proj.enabled) : true
    }

    const resetForm = () => {
      isEdit.value = false
      editingId.value = null
      form.title = ''
      form.subtitle = ''
      form.category = 'EKSPERİMENT'
      form.image = ''
      form.duration = '15:00'
      form.youtube_url = ''
      form.description = ''
      form.sort_order = 0
      form.enabled = true
    }

    const save = async () => {
      saving.value = true
      try {
        if (!form.image && form.youtube_url) {
          form.image = getYoutubeThumb(form.youtube_url)
        }
        if (isEdit.value) {
          await client.put(`/projects/${editingId.value}`, form)
        } else {
          await client.post('/projects', form)
        }
        resetForm()
        await fetchProjects()
      } catch (err) {
        alert(err.response?.data?.error || 'Xəta baş verdi')
      } finally {
        saving.value = false
      }
    }

    const confirmDelete = async (proj) => {
      if (confirm(`"${proj.title}" layihəsini silmək istədiyinizdən əminsiniz?`)) {
        try {
          await client.delete(`/projects/${proj.id}`)
          await fetchProjects()
        } catch (err) {
          alert('Xəta baş verdi')
        }
      }
    }

    return {
      projects,
      isEdit,
      saving,
      form,
      getYoutubeThumb,
      onYoutubeChange,
      fetchYtThumb,
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
  grid-template-columns: 1.3fr 0.7fr;
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
.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.panel-title {
  font-size: 15px;
  font-weight: 600;
  margin: 0;
  color: #fff;
}
.count-badge {
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 9999px;
  background-color: rgba(252, 219, 86, 0.15);
  color: var(--color-primary);
}
.table-wrapper {
  overflow-x: auto;
}
.table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}
.table th,
.table td {
  padding: 14px 16px;
  border-bottom: 1px solid var(--color-border);
  font-size: 13px;
  vertical-align: middle;
}
.table th {
  background-color: rgba(255, 255, 255, 0.01);
  color: var(--color-text-secondary);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 10px;
  letter-spacing: 0.05em;
}
.project-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}
.project-thumb {
  width: 50px;
  height: 75px;
  border-radius: 6px;
  object-fit: cover;
  object-position: top;
  border: 1px solid var(--color-border);
  background-color: #080117;
  flex-shrink: 0;
}
.project-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;
}
.project-title {
  font-weight: 600;
  color: #fff;
  font-size: 13px;
  line-height: 1.3;
}
.project-subtitle {
  font-size: 11px;
  color: var(--color-text-secondary);
  line-height: 1.2;
}
.yt-link {
  color: var(--color-primary);
  text-decoration: none;
  font-size: 11px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  margin-top: 2px;
}
.yt-link:hover {
  text-decoration: underline;
}
.icon-inline {
  width: 10px;
  height: 10px;
}
.meta-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.badge-cat {
  display: inline-block;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  color: #080117;
  background-color: #fcdb56;
  padding: 2px 6px;
  border-radius: 4px;
  width: fit-content;
}
.badge-dur {
  font-size: 10px;
  font-family: monospace;
  color: var(--color-text-secondary);
}
.status-badge {
  font-size: 10px;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: 9999px;
  text-transform: uppercase;
}
.status-badge.active {
  background-color: rgba(34, 197, 94, 0.15);
  color: #4ade80;
  border: 1px solid rgba(34, 197, 94, 0.3);
}
.status-badge.inactive {
  background-color: rgba(239, 68, 68, 0.15);
  color: #f87171;
  border: 1px solid rgba(239, 68, 68, 0.3);
}
.actions {
  display: flex;
  gap: 8px;
}
.action-btn {
  font-size: 11px;
  padding: 5px 10px;
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
  background-color: rgba(252, 219, 86, 0.1);
  border-color: var(--color-primary);
}
.action-btn.delete {
  color: var(--color-red, #ef4444);
}
.action-btn.delete:hover {
  background-color: rgba(239, 68, 68, 0.1);
  border-color: var(--color-red, #ef4444);
}
.form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.form-row {
  display: flex;
  gap: 12px;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.flex-1 {
  flex: 1;
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
.poster-preview-box {
  margin-top: 8px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.preview-label {
  font-size: 10px;
  color: var(--color-text-secondary);
}
.poster-preview-img {
  width: 90px;
  height: 140px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid var(--color-border);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}
.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 12px;
  color: #fff;
  user-select: none;
}
.checkbox {
  width: 16px;
  height: 16px;
  cursor: pointer;
  accent-color: var(--color-primary);
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
  border: none;
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

@media (max-width: 1024px) {
  .grid {
    grid-template-columns: 1fr;
  }
}
</style>
