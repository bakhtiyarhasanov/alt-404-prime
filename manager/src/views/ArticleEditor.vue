<template>
  <div class="editor-view">
    <div v-if="isEditingDisabled" class="alert-banner">
      Məqalə dərc olunub. Müxbirlər dərc edilmiş məqalələri redaktə edə bilməz.
    </div>

    <form @submit.prevent="save" class="editor-form">
      <div class="main-column">
        <!-- Title & Content -->
        <div class="panel">
          <div class="creator-row" v-if="form.creator_name">
            <span class="creator-badge">Müəllif: <strong>{{ form.creator_name }}</strong></span>
          </div>

          <div class="form-group">
            <label class="label">Məqalə Başlığı</label>
            <input v-model="form.title" type="text" placeholder="Başlıq daxil edin..." class="input input-lg" :disabled="isEditingDisabled" required>
          </div>

          <div class="form-group">
            <label class="label">Slug (Keçid ünvanı)</label>
            <input v-model="form.slug" type="text" placeholder="avtomatik-yaradilir-ve-ya-ozunuz-yazin" class="input" :disabled="isEditingDisabled">
          </div>

          <div class="form-group">
            <label class="label">Qısa Xülasə (Excerpt)</label>
            <textarea v-model="form.excerpt" rows="3" placeholder="Qısa məzmun xülasəsi..." class="input" :disabled="isEditingDisabled"></textarea>
          </div>

          <div class="form-group">
            <label class="label">Əsas Məzmun (Zəngin Mətn Redaktoru)</label>
            <div ref="editorElement" class="quill-editor-container"></div>
          </div>
        </div>

        <!-- News Edit History (visible to editors only under news form) -->
        <div v-if="user.role === 'editor' && form.history && form.history.length > 0" class="panel history-panel">
          <h3 class="panel-title">Redaktə Tarixçəsi</h3>
          <div class="history-list">
            <div v-for="hist in form.history" :key="hist.id" class="history-item">
              <div class="history-meta">
                <span class="history-user">{{ hist.user_name }}</span>
                <span class="history-date">{{ formatDateTime(hist.created_at) }}</span>
              </div>
              <div class="history-changes">
                <div v-for="(change, field) in hist.changes" :key="field" class="change-detail">
                  <span class="change-field">{{ translateField(field) }}:</span>
                  <span class="old-val">{{ formatValue(change.old) }}</span>
                  <span class="change-arrow">&rarr;</span>
                  <span class="new-val">{{ formatValue(change.new) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="side-column">
        <div class="panel">
          <!-- Save actions -->
          <div class="form-group">
            <button type="submit" class="btn-primary w-full" :disabled="saving || isEditingDisabled">
              {{ saving ? 'Yadda saxlanılır...' : 'Yadda Saxla' }}
            </button>
            <button type="button" @click="$router.push('/articles')" class="btn-ghost w-full style-margin">Ləğv et</button>
          </div>

          <div class="divider"></div>

          <!-- Configuration details -->
          <div class="form-group">
            <label class="label">Kateqoriya</label>
            <select v-model="form.category" class="input" :disabled="isEditingDisabled" required>
              <option v-for="cat in categories" :key="cat.slug" :value="cat.slug">{{ cat.label }}</option>
            </select>
          </div>

          <div class="form-group">
            <label class="label">Əsas Şəkil URL</label>
            <div class="image-input-row">
              <input v-model="form.image_url" type="text" class="input flex-1" placeholder="https://..." :disabled="isEditingDisabled">
              <input type="file" ref="fileInput" @change="uploadImage" style="display:none" accept="image/*">
              <button type="button" @click="$refs.fileInput.click()" class="btn-secondary" :disabled="uploadingImage || isEditingDisabled">
                {{ uploadingImage ? 'Yüklənir...' : 'Şəkil Yüklə' }}
              </button>
            </div>
            <div v-if="form.image_url" class="image-preview-container">
              <img v-show="!imageError" :src="form.image_url" alt="Şəkil önbaxışı" class="image-preview" @error="handleImageError">
              <span v-if="imageError" class="preview-error">Şəkil yüklənə bilmədi (URL-i yoxlayın)</span>
            </div>
          </div>

          <div class="form-group">
            <label class="label">Teqlər (Virgüllə ayırın)</label>
            <input v-model="tagsInput" type="text" class="input" placeholder="AI, startap, tech" :disabled="isEditingDisabled">
          </div>

          <div class="divider"></div>

          <!-- Flags -->
          <div class="form-group check-group">
            <input v-model="form.published" type="checkbox" id="published" :disabled="isEditingDisabled || user.role === 'reporter'">
            <label for="published" class="check-label">Dərc edilsin <span v-if="user.role === 'reporter'" class="role-note">(Redaktor təsdiqi lazımdır)</span></label>
          </div>

          <div class="form-group check-group">
            <input v-model="form.featured" type="checkbox" id="featured" :disabled="isEditingDisabled || user.role === 'reporter'">
            <label for="featured" class="check-label">Seçilmiş (Featured)</label>
          </div>

          <div class="form-group check-group">
            <input v-model="form.updating" type="checkbox" id="updating" :disabled="isEditingDisabled">
            <label for="updating" class="check-label">Yenilənir (Updating)</label>
          </div>

          <div class="divider"></div>

          <div class="form-group">
            <label class="label">Başlama Tarixi (Start Time)</label>
            <input v-model="form.start_time" type="datetime-local" class="input" :disabled="isEditingDisabled">
          </div>

          <div class="form-group">
            <label class="label">Bitmə Tarixi (End Time)</label>
            <input v-model="form.end_time" type="datetime-local" class="input" :disabled="isEditingDisabled">
          </div>

          <!-- Revision History Section -->
          <div v-if="form.versions && form.versions.length > 0" class="divider"></div>
          <div v-if="form.versions && form.versions.length > 0" class="versions-section">
            <label class="label">Dəyişikliklər Tarixçəsi</label>
            <div class="versions-list">
              <div v-for="v in form.versions" :key="v.version" class="version-item">
                <span class="v-badge">v{{ v.version }}</span>
                <div class="v-info">
                  <span class="v-author">{{ v.author || 'Admin' }}</span>
                  <span class="v-date">{{ formatDateTime(v.created_at) }}</span>
                </div>
                <button type="button" @click="restoreVersion(v)" class="restore-btn" :disabled="isEditingDisabled">Geri Qaytar</button>
              </div>
            </div>
          </div>

        </div>
      </div>
    </form>
  </div>
</template>

<script>
import { ref, reactive, onMounted, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import client from '../api/client'
import { useAuthStore } from '../stores/auth'
import Quill from 'quill'
import 'quill/dist/quill.snow.css'

export default {
  name: 'ArticleEditor',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const authStore = useAuthStore()
    const isEdit = ref(false)
    const saving = ref(false)
    const categories = ref([])
    const tagsInput = ref('')
    const editorElement = ref(null)
    const imageError = ref(false)
    const isOriginallyPublished = ref(false)
    let quill = null

    const form = reactive({
      title: '',
      slug: '',
      excerpt: '',
      content: '',
      category: 'texnologiya',
      image_url: '',
      published: true,
      featured: false,
      updating: false,
      start_time: '',
      end_time: '',
      versions: [],
      creator_name: '',
      history: []
    })

    const user = computed(() => authStore.user || {})

    const isEditingDisabled = computed(() => {
      // reporter cannot edit published news
      return isEdit.value && user.value.role === 'reporter' && isOriginallyPublished.value
    })

    // Reset error when URL changes
    watch(() => form.image_url, () => {
      imageError.value = false
    })

    const handleImageError = () => {
      imageError.value = true
    }

    const loadArticle = async (id) => {
      try {
        const { data } = await client.get(`/articles/${id}`)
        form.title = data.title
        form.slug = data.slug
        form.excerpt = data.excerpt
        form.content = data.content
        form.category = data.category
        form.image_url = data.image_url
        form.published = data.published
        form.featured = data.featured
        form.updating = data.updating
        form.versions = data.versions || []
        form.creator_name = data.creator_name || ''
        form.history = data.history || []
        tagsInput.value = (data.tags || []).join(', ')

        form.start_time = data.start_time ? toDatetimeLocal(data.start_time) : ''
        form.end_time = data.end_time ? toDatetimeLocal(data.end_time) : ''

        isOriginallyPublished.value = (data.published === true || parseInt(data.published) === 1)

        // Load content into editor if set and changed
        if (quill && data.content && quill.root.innerHTML !== data.content) {
          quill.root.innerHTML = data.content
          if (isEditingDisabled.value) {
            quill.disable()
          }
        }
      } catch (err) {
        console.error(err)
      }
    }

    onMounted(async () => {
      // Initialize Quill Editor
      if (editorElement.value) {
        quill = new Quill(editorElement.value, {
          theme: 'snow',
          placeholder: 'Məqalə məzmununu buraya daxil edin...',
          modules: {
            toolbar: [
              [{ 'header': [1, 2, 3, 4, false] }],
              ['bold', 'italic', 'underline', 'strike'],
              [{ 'color': [] }, { 'background': [] }],
              [{ 'list': 'ordered'}, { 'list': 'bullet' }],
              [{ 'align': [] }],
              ['blockquote', 'code-block'],
              ['link', 'image'],
              ['clean']
            ]
          }
        })

        // Sync editor changes to state
        quill.on('text-change', () => {
          form.content = quill.root.innerHTML
        })
      }

      // Get categories
      try {
        const catRes = await client.get('/categories')
        categories.value = catRes.data

        if (route.params.id) {
          isEdit.value = true
          await loadArticle(route.params.id)
        } else {
          // If creating new and reporter, default published to false
          if (user.value.role === 'reporter') {
            form.published = false
          }
        }
      } catch (err) {
        console.error(err)
      }
    })

    const save = async () => {
      if (isEditingDisabled.value) return
      saving.value = true
      // Parse tags
      const tags = tagsInput.value
        .split(',')
        .map(t => t.trim())
        .filter(t => t.length > 0)

      const payload = {
        title: form.title,
        slug: form.slug,
        excerpt: form.excerpt,
        content: form.content,
        category: form.category,
        image_url: form.image_url,
        published: user.value.role === 'reporter' ? false : form.published,
        featured: user.value.role === 'reporter' ? false : form.featured,
        updating: form.updating,
        start_time: form.start_time || null,
        end_time: form.end_time || null,
        tags
      }

      try {
        if (isEdit.value) {
          await client.put(`/articles/${route.params.id}`, payload)
          alert('Məqalə uğurla yadda saxlanıldı')
          await loadArticle(route.params.id)
        } else {
          const { data } = await client.post('/articles', payload)
          alert('Məqalə uğurla yaradıldı')
          isEdit.value = true
          router.replace(`/articles/edit/${data.id}`)
          // Wait for router parameters to update, then load details
          setTimeout(() => {
            loadArticle(data.id)
          }, 50)
        }
      } catch (err) {
        alert(err.response?.data?.error || 'Məqalə saxlanarkən xəta baş verdi')
      } finally {
        saving.value = false
      }
    }

    const toDatetimeLocal = (dateStr) => {
      if (!dateStr) return ''
      const clean = dateStr.replace(' ', 'T')
      return clean.substring(0, 16)
    }

    const formatDateTime = (dateStr) => {
      if (!dateStr) return ''
      const d = new Date(dateStr)
      return d.toLocaleDateString('az-AZ', { day: 'numeric', month: 'short' }) + ' ' + d.toLocaleTimeString('az-AZ', { hour: '2-digit', minute: '2-digit' })
    }

    const uploadingImage = ref(false)
    const fileInput = ref(null)

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
        
        // Ensure local uploads reference the backend host (e.g. http://localhost:8000/uploads/...)
        // client.defaults.baseURL is usually 'http://localhost:8000/api' or '/api'
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

    const restoreVersion = (v) => {
      if (confirm(`Məqalənin v${v.version} versiyasına geri qaytarmaq istədiyinizdən əminsiniz? (Saxlamadığınız digər dəyişikliklər silinəcək)`)) {
        form.title = v.title
        form.content = v.content
        if (quill) {
          quill.root.innerHTML = v.content || ''
        }
      }
    }

    const translateField = (field) => {
      const fieldTranslations = {
        title: 'Başlıq',
        excerpt: 'Qısa Xülasə',
        content: 'Məzmun',
        category: 'Kateqoriya',
        image_url: 'Şəkil URL',
        featured: 'Seçilmiş',
        published: 'Dərc Durumu',
        updating: 'Yenilənir',
        tags: 'Teqlər'
      }
      return fieldTranslations[field] || field
    }

    const formatValue = (val) => {
      if (val === true || val === 1) return 'Bəli / Aktiv'
      if (val === false || val === 0) return 'Xeyr / Passiv'
      if (Array.isArray(val)) return val.join(', ')
      return val
    }

    return {
      form,
      saving,
      categories,
      tagsInput,
      editorElement,
      imageError,
      handleImageError,
      save,
      formatDateTime,
      restoreVersion,
      uploadingImage,
      fileInput,
      uploadImage,
      user,
      isEditingDisabled,
      translateField,
      formatValue
    }
  }
}
</script>

<style scoped>
.editor-form {
  display: flex;
  gap: 24px;
}
.main-column {
  flex: 1;
  min-width: 0;
}
.side-column {
  width: 320px;
  flex-shrink: 0;
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
.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.label {
  font-size: 12px;
  color: var(--color-text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
}
.input {
  background-color: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 10px 14px;
  color: #fff;
  font-family: inherit;
  outline: none;
  font-size: 14px;
}
.input:focus {
  border-color: var(--color-primary);
}
.input-lg {
  font-size: 18px;
  font-weight: 600;
}
.code-editor {
  font-family: 'Tomorrow', monospace;
  font-size: 13px;
  line-height: 1.6;
}
.btn-primary {
  background-color: var(--color-primary);
  color: #111;
  font-weight: 600;
  padding: 12px;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s;
  width: 100%;
}
.btn-primary:hover {
  background-color: var(--color-primary-hover);
  transform: translateY(-1px);
}
.btn-ghost {
  border: 1px solid var(--color-border);
  color: var(--color-text-secondary);
  padding: 12px;
  border-radius: 8px;
  font-size: 14px;
  background: none;
  transition: all 0.2s;
  width: 100%;
}
.btn-ghost:hover {
  background-color: var(--color-surface-hover);
  color: #fff;
}
.w-full {
  width: 100%;
}
.style-margin {
  margin-top: 8px;
}
.divider {
  height: 1px;
  background-color: var(--color-border);
}
.check-group {
  flex-direction: row;
  align-items: center;
  gap: 10px;
  cursor: pointer;
}
.check-label {
  font-size: 13px;
  color: var(--color-text-primary);
  user-select: none;
}
.versions-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.versions-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.version-item {
  display: flex;
  align-items: center;
  gap: 8px;
  background-color: rgba(255,255,255,0.02);
  border: 1px solid var(--color-border);
  border-radius: 6px;
  padding: 8px;
  font-size: 12px;
}
.v-badge {
  background: rgba(252,219,86,0.1);
  color: var(--color-primary);
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: 600;
  font-size: 10px;
}
.v-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}
.v-author {
  color: #fff;
  font-weight: 600;
}
.v-date {
  color: var(--color-text-muted);
  font-size: 10px;
}
.restore-btn {
  font-size: 10px;
  color: var(--color-primary);
  background: none;
  border: 1px solid rgba(252,219,86,0.2);
  padding: 4px 8px;
  border-radius: 4px;
  cursor: pointer;
}
.restore-btn:hover {
  background: rgba(252,219,86,0.1);
}

/* Quill Editor Dark Theme Styling */
.quill-editor-container {
  min-height: 380px;
  background-color: var(--color-bg);
  color: var(--color-text-primary);
  border-radius: 0 0 8px 8px;
}
:deep(.ql-toolbar.ql-snow) {
  border-color: var(--color-border);
  background-color: var(--color-surface);
  border-radius: 8px 8px 0 0;
  border-bottom: none;
}
:deep(.ql-container.ql-snow) {
  border-color: var(--color-border);
  border-radius: 0 0 8px 8px;
  font-family: inherit;
  font-size: 14px;
}
:deep(.ql-snow .ql-stroke) {
  stroke: var(--color-text-secondary);
}
:deep(.ql-snow .ql-fill) {
  fill: var(--color-text-secondary);
}
:deep(.ql-snow .ql-picker) {
  color: var(--color-text-secondary);
}
:deep(.ql-snow .ql-picker-options) {
  background-color: var(--color-surface);
  border-color: var(--color-border);
}
:deep(.ql-snow .ql-picker-item) {
  color: var(--color-text-secondary);
}
:deep(.ql-snow .ql-picker-item:hover),
:deep(.ql-snow .ql-picker-item.ql-selected) {
  color: var(--color-primary);
}
:deep(.ql-snow.ql-toolbar button:hover),
:deep(.ql-snow.ql-toolbar button.ql-active),
:deep(.ql-snow .ql-picker-label:hover),
:deep(.ql-snow .ql-picker-label.ql-active) {
  color: var(--color-primary);
}
:deep(.ql-snow.ql-toolbar button:hover .ql-stroke),
:deep(.ql-snow.ql-toolbar button.ql-active .ql-stroke),
:deep(.ql-snow .ql-picker-label:hover .ql-stroke),
:deep(.ql-snow .ql-picker-label.ql-active .ql-stroke) {
  stroke: var(--color-primary);
}
:deep(.ql-snow.ql-toolbar button:hover .ql-fill),
:deep(.ql-snow.ql-toolbar button.ql-active .ql-fill),
:deep(.ql-snow .ql-picker-label:hover .ql-fill),
:deep(.ql-snow .ql-picker-label.ql-active .ql-fill) {
  fill: var(--color-primary);
}
:deep(.ql-editor.ql-blank::before) {
  color: var(--color-text-muted);
  font-style: normal;
}
:deep(.ql-editor) {
  min-height: 380px;
}

/* Image Preview Styles */
.image-preview-container {
  margin-top: 10px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid var(--color-border);
  background-color: var(--color-bg);
  position: relative;
  aspect-ratio: 16/9;
  display: flex;
  align-items: center;
  justify-content: center;
}
.image-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.preview-error {
  position: absolute;
  font-size: 11px;
  color: var(--color-red);
  background-color: rgba(0, 0, 0, 0.85);
  padding: 6px 10px;
  border-radius: 4px;
  text-align: center;
}

.image-input-row {
  display: flex;
  gap: 8px;
}
.flex-1 {
  flex: 1;
}
.btn-secondary {
  background-color: var(--color-surface-hover);
  border: 1px solid var(--color-border);
  color: #fff;
  font-weight: 600;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 13px;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s;
}
.btn-secondary:hover:not(:disabled) {
  background-color: var(--color-border);
}
.btn-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* New Role & History Styles */
.alert-banner {
  background-color: rgba(239, 68, 68, 0.15);
  color: var(--color-red);
  padding: 16px 20px;
  border-radius: 12px;
  margin-bottom: 24px;
  font-weight: 600;
  border: 1px solid rgba(239, 68, 68, 0.25);
  font-size: 14px;
}
.creator-row {
  margin-bottom: 16px;
}
.creator-badge {
  background-color: var(--color-bg);
  border: 1px solid var(--color-border);
  color: var(--color-text-secondary);
  font-size: 12px;
  padding: 6px 12px;
  border-radius: 6px;
  display: inline-block;
}
.role-note {
  font-size: 11px;
  color: var(--color-text-muted);
  font-weight: normal;
  display: block;
}
.history-panel {
  margin-top: 24px;
}
.history-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.history-item {
  background-color: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 16px;
}
.history-meta {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  font-size: 12px;
  border-bottom: 1px dashed var(--color-border);
  padding-bottom: 8px;
}
.history-user {
  font-weight: 600;
  color: var(--color-primary);
}
.history-date {
  color: var(--color-text-muted);
}
.history-changes {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.change-detail {
  font-size: 12px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px;
}
.change-field {
  font-weight: 600;
  color: var(--color-text-primary);
  min-width: 100px;
}
.old-val {
  color: var(--color-red);
  text-decoration: line-through;
  background-color: rgba(239, 68, 68, 0.1);
  padding: 2px 6px;
  border-radius: 4px;
  word-break: break-all;
}
.new-val {
  color: var(--color-green);
  background-color: rgba(16, 185, 129, 0.1);
  padding: 2px 6px;
  border-radius: 4px;
  word-break: break-all;
}
.change-arrow {
  color: var(--color-text-muted);
}
</style>
