<template>
  <div class="settings-view">
    <div class="header-row">
      <h2 class="page-title">Sistem və Səhifə Tənzimləmələri</h2>
    </div>

    <!-- Grouped Settings Panels -->
    <div v-for="group in groupedSettings" :key="group.name" class="settings-group-panel panel">
      <h3 class="group-title">{{ getGroupNameTranslation(group.name) }}</h3>
      
      <div class="settings-grid">
        <div v-for="setting in group.items" :key="setting.key" class="setting-card">
          <div class="setting-info">
            <span class="setting-label">{{ setting.label }}</span>
            <span class="setting-key-badge">{{ setting.key }}</span>
          </div>

          <!-- Value Preview -->
          <div class="setting-preview">
            <!-- File Preview -->
            <div v-if="setting.type === 'file'" class="file-preview">
              <a v-if="setting.value" :href="setting.value" target="_blank" class="file-link">
                <svg class="file-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Fayla Bax
              </a>
              <span v-else class="empty-preview">Fayl yoxdur</span>
            </div>
            
            <!-- Rich Text Preview -->
            <div v-else-if="setting.type === 'rich_text'" class="html-preview" v-html="truncateHtml(setting.value)"></div>
            
            <!-- Default Text Preview -->
            <div v-else class="text-preview">{{ setting.value || 'Dəyər daxil edilməyib' }}</div>
          </div>

          <div class="setting-actions">
            <button @click="openEditModal(setting)" class="btn-ghost btn-sm">Düzəliş Et</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content panel">
        <div class="modal-header">
          <h2 class="panel-title">Tənzimləmə: {{ selectedSetting?.label }}</h2>
          <button @click="closeModal" class="close-btn">&times;</button>
        </div>

        <form @submit.prevent="saveSetting" class="form">
          <!-- Short Text Input -->
          <div v-if="selectedSetting?.type === 'short_text'" class="form-group">
            <label class="label">Mətn</label>
            <input v-model="form.value" type="text" class="input" required placeholder="Dəyər daxil edin...">
          </div>

          <!-- Medium Text Textarea -->
          <div v-else-if="selectedSetting?.type === 'medium_text'" class="form-group">
            <label class="label">Uzun Mətn</label>
            <textarea v-model="form.value" class="textarea" rows="5" required placeholder="Mətni daxil edin..."></textarea>
          </div>

          <!-- Rich Text Quill Editor -->
          <div v-else-if="selectedSetting?.type === 'rich_text'" class="form-group">
            <label class="label">Formatlı Mətn (Zəngin Redaktor)</label>
            <div ref="editorElement" class="quill-editor-container"></div>
          </div>

          <!-- File Upload -->
          <div v-else-if="selectedSetting?.type === 'file'" class="form-group">
            <label class="label">Fayl Yüklə (Şəkil və s.)</label>
            <div class="upload-wrapper">
              <input v-model="form.value" type="text" class="input" placeholder="/uploads/..." readonly>
              <input type="file" ref="fileInput" @change="handleFileUpload" style="display:none">
              <button type="button" @click="$refs.fileInput.click()" class="btn-ghost" :disabled="uploading">
                {{ uploading ? 'Yüklənir...' : 'Fayl Seç və Yüklə' }}
              </button>
            </div>
          </div>

          <div class="actions-row">
            <button type="submit" class="btn-primary" :disabled="saving || uploading">
              {{ saving ? 'Saxlanılır...' : 'Yadda Saxla' }}
            </button>
            <button type="button" @click="closeModal" class="btn-ghost">Ləğv Et</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, nextTick, onMounted } from 'vue'
import client from '../api/client'
import Quill from 'quill'
import 'quill/dist/quill.snow.css'

export default {
  name: 'SettingsView',
  setup() {
    const settings = ref([])
    const selectedSetting = ref(null)
    const showModal = ref(false)
    const saving = ref(false)
    const uploading = ref(false)
    const editorElement = ref(null)
    let quillInstance = null

    const form = ref({
      value: ''
    })

    const fetchSettings = async () => {
      try {
        const { data } = await client.get('/settings')
        settings.value = data
      } catch (err) {
        console.error('Tənzimləmələr yüklənərkən xəta:', err)
      }
    }

    onMounted(fetchSettings)

    // Grouping logic
    const groupedSettings = computed(() => {
      const groups = {}
      settings.value.forEach(item => {
        if (!groups[item.group_name]) {
          groups[item.group_name] = []
        }
        groups[item.group_name].push(item)
      })
      return Object.keys(groups).map(name => ({
        name,
        items: groups[name]
      }))
    })

    const getGroupNameTranslation = (name) => {
      const translations = {
        social: 'Sosial Şəbəkə Linkləri',
        about: 'Haqqımızda Səhifəsi',
        terms: 'İstifadə Qaydaları (Şərtlər)',
        cookies: 'Çərəz Siyasəti (Cookies)',
        contact: 'Əlaqə Səhifəsi'
      }
      return translations[name] || name
    }

    const truncateHtml = (html) => {
      if (!html) return 'Dəyər daxil edilməyib'
      const tmp = document.createElement('DIV')
      tmp.innerHTML = html
      const text = tmp.textContent || tmp.innerText || ''
      return text.length > 100 ? text.substring(0, 100) + '...' : text
    }

    const openEditModal = (setting) => {
      selectedSetting.value = setting
      form.value.value = setting.value || ''
      showModal.value = true

      if (setting.type === 'rich_text') {
        nextTick(() => {
          if (editorElement.value) {
            quillInstance = new Quill(editorElement.value, {
              theme: 'snow',
              placeholder: 'Məzmunu daxil edin...',
              modules: {
                toolbar: [
                  [{ 'header': [1, 2, 3, 4, false] }],
                  ['bold', 'italic', 'underline', 'strike'],
                  ['blockquote', 'code-block'],
                  [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                  ['link', 'image'],
                  ['clean']
                ]
              }
            })
            quillInstance.root.innerHTML = setting.value || ''
          }
        })
      }
    }

    const closeModal = () => {
      showModal.value = false
      selectedSetting.value = null
      quillInstance = null
    }

    const handleFileUpload = async (e) => {
      const file = e.target.files[0]
      if (!file) return
      uploading.value = true
      const formData = new FormData()
      formData.append('file', file)

      try {
        const { data } = await client.post(`/settings/${selectedSetting.value.key}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        form.value.value = data.value
      } catch (err) {
        alert('Fayl yüklənərkən xəta baş verdi.')
      } finally {
        uploading.value = false
      }
    }

    const saveSetting = async () => {
      saving.value = true
      
      // If rich text, get content from Quill
      if (selectedSetting.value.type === 'rich_text' && quillInstance) {
        form.value.value = quillInstance.root.innerHTML
      }

      try {
        await client.put(`/settings/${selectedSetting.value.key}`, {
          value: form.value.value
        })
        await fetchSettings()
        closeModal()
      } catch (err) {
        alert('Tənzimləmə saxlanarkən xəta baş verdi.')
      } finally {
        saving.value = false
      }
    }

    return {
      settings,
      groupedSettings,
      getGroupNameTranslation,
      truncateHtml,
      selectedSetting,
      showModal,
      form,
      saving,
      uploading,
      editorElement,
      openEditModal,
      closeModal,
      handleFileUpload,
      saveSetting
    }
  }
}
</script>

<style scoped>
.settings-view {
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.header-row {
  margin-bottom: 8px;
}
.panel {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 24px;
}
.settings-group-panel {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.group-title {
  font-size: 16px;
  font-weight: 600;
  color: var(--color-primary);
  margin: 0;
  padding-bottom: 8px;
  border-bottom: 1px solid var(--color-border);
}
.settings-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}
@media (max-width: 768px) {
  .settings-grid {
    grid-template-columns: 1fr;
  }
}
.setting-card {
  background-color: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 12px;
}
.setting-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.setting-label {
  font-size: 14px;
  font-weight: 600;
  color: #fff;
}
.setting-key-badge {
  font-family: monospace;
  font-size: 11px;
  color: var(--color-text-muted);
}
.setting-preview {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 6px;
  padding: 8px 12px;
  font-size: 13px;
  color: var(--color-text-secondary);
  min-height: 40px;
  max-height: 80px;
  overflow: hidden;
  display: flex;
  align-items: center;
}
.text-preview {
  word-break: break-all;
}
.html-preview {
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}
.file-preview {
  display: flex;
  align-items: center;
}
.file-link {
  display: flex;
  align-items: center;
  gap: 6px;
  color: var(--color-primary);
  text-decoration: none;
}
.file-link:hover {
  text-decoration: underline;
}
.file-icon {
  width: 14px;
  height: 14px;
}
.empty-preview {
  color: var(--color-text-muted);
  font-style: italic;
}
.setting-actions {
  display: flex;
  justify-content: flex-end;
}
.btn-ghost {
  border: 1px solid var(--color-border);
  background: none;
  color: var(--color-text-secondary);
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-ghost:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
  background-color: var(--color-surface-hover);
}
.btn-sm {
  padding: 4px 8px;
  font-size: 11px;
}

/* Modal styling */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.65);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 16px;
}
.modal-content {
  width: 100%;
  max-width: 650px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.modal-header {
  display: flex;
  justify-content: justify;
  align-items: center;
  justify-content: space-between;
}
.panel-title {
  font-size: 16px;
  font-weight: 600;
  margin: 0;
}
.close-btn {
  background: none;
  border: none;
  color: var(--color-text-secondary);
  font-size: 24px;
  cursor: pointer;
}
.close-btn:hover {
  color: #fff;
}
.form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.label {
  font-size: 13px;
  font-weight: 500;
  color: var(--color-text-secondary);
}
.input, .textarea {
  background-color: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 10px 14px;
  color: #fff;
  font-size: 14px;
  outline: none;
  font-family: inherit;
}
.input:focus, .textarea:focus {
  border-color: var(--color-primary);
}
.upload-wrapper {
  display: flex;
  gap: 8px;
}
.upload-wrapper .input {
  flex: 1;
}
.quill-editor-container {
  height: 240px;
  background-color: var(--color-bg);
  border-radius: 8px;
  color: #fff;
  border: 1px solid var(--color-border);
  overflow: hidden;
}
.actions-row {
  display: flex;
  gap: 12px;
  margin-top: 8px;
}
.btn-primary {
  background-color: var(--color-primary);
  color: #111;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}
.btn-primary:hover {
  background-color: var(--color-primary-hover);
}
</style>
